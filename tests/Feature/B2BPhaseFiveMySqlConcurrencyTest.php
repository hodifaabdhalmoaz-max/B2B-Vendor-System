<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProductVariantService;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/** Opt-in destructive test against an explicitly named disposable MySQL database. */
class B2BPhaseFiveMySqlConcurrencyTest extends TestCase
{
    public function test_two_connection_inventory_reservation_and_state_races(): void
    {
        $database = getenv('B2B_MYSQL_CONCURRENCY_DATABASE') ?: '';
        if ($database === '' || getenv('B2B_MYSQL_CONCURRENCY_ALLOW_FRESH') !== '1') {
            $this->markTestSkipped('No explicit disposable MySQL database and migrate:fresh opt-in; SQLite cannot verify InnoDB row-lock races.');
        }
        if (! str_ends_with($database, '_test') && ! str_ends_with($database, '_testing')) {
            $this->fail('The opt-in MySQL concurrency database must end in _test or _testing.');
        }

        $original = DB::getDefaultConnection();
        $originalMysqlDatabase = config('database.connections.mysql.database');
        $originalMysqlUrl = config('database.connections.mysql.url');
        config(['database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        try {
            $this->assertSame($database, DB::connection()->getDatabaseName());
            $this->assertSame(0, Artisan::call('migrate:fresh', ['--force' => true]));
            $this->assertSame(0, Artisan::call('b2b:verify-schema'), Artisan::output());
            foreach (['product_variants', 'inventory_items', 'inventory_movements', 'reservations', 'reservation_items'] as $table) {
                $engine = DB::selectOne('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$database, $table]);
                $this->assertSame('innodb', strtolower($engine->engine ?? ''), "$table requires InnoDB");
            }
            $this->assertSame(1, (int) DB::selectOne('SELECT @@SESSION.foreign_key_checks AS enabled')->enabled);
            try {
                $isolation = DB::selectOne('SELECT @@SESSION.transaction_isolation AS level')->level;
            } catch (\Illuminate\Database\QueryException) {
                $isolation = DB::selectOne('SELECT @@SESSION.tx_isolation AS level')->level;
            }
            fwrite(STDOUT, "\nMySQL gate: database=$database; engine=InnoDB; foreign_key_checks=1; isolation=$isolation\n");

            // Gate 0: two independent service calls compete for one available unit.
            $inventoryVariant = $this->variant('MYSQL-INV');
            $results = $this->race(['reserve', 'reserve'], $inventoryVariant->id, 'gate-0');
            $this->assertSame(1, collect($results)->where('ok', true)->count());
            $this->assertSame(1, collect($results)->where('ok', false)->count());
            $this->assertSame(1, $inventoryVariant->inventoryItem->fresh()->stock_on_hand);
            $this->assertSame(1, $inventoryVariant->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(0, $inventoryVariant->inventoryItem->fresh()->available_quantity);

            // Two independent creation transactions compete for a second unit.
            $reservationVariant = $this->variant('MYSQL-RES');
            $profile = $this->profile();
            $competitor = $this->profile();
            $this->assertNotSame($profile->id, $competitor->id);
            $results = $this->race(['create', 'create'], [$profile->id, $competitor->id], (string) $reservationVariant->id);
            $this->assertSame(1, collect($results)->where('ok', true)->count());
            $this->assertSame(1, collect($results)->where('ok', false)->count());
            $this->assertSame(1, Reservation::count());
            $this->assertSame(1, $reservationVariant->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(1, InventoryMovement::where('product_variant_id', $reservationVariant->id)->where('type', 'reserve')->count());

            // Identical concurrent keys replay the committed winner without a second hold.
            $replayVariant = $this->variant('MYSQL-IDP');
            $results = $this->race(['create_same_key', 'create_same_key'], $profile->id, (string) $replayVariant->id);
            $this->assertSame(2, collect($results)->where('ok', true)->count());
            $this->assertSame($results[0]['reservation_id'], $results[1]['reservation_id']);
            $this->assertSame(1, $replayVariant->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(1, InventoryMovement::where('product_variant_id', $replayVariant->id)->where('type', 'reserve')->count());
            $this->assertSame(1, DB::table('notifications')->where('event_key', 'reservation:'.$results[0]['reservation_id'].':created')->count());

            // A due reservation may be confirmed or expired, never both.
            $transitionVariant = $this->variant('MYSQL-STATE');
            $due = app(ReservationService::class)->create($profile, [['product_variant_id' => $transitionVariant->id, 'quantity' => 1]], 'mysql-due');
            DB::table('reservations')->where('id', $due->id)->update(['expires_at' => now()->subMinute()]);
            $results = $this->race(['confirm', 'expire'], $due->id, 'state');
            $this->assertSame(1, collect($results)->where('ok', true)->count());
            $this->assertSame(1, collect($results)->where('ok', false)->count());
            $this->assertContains($due->fresh()->status, [Reservation::STATUS_CONFIRMED, Reservation::STATUS_EXPIRED]);
            $this->assertSame($due->fresh()->status === Reservation::STATUS_CONFIRMED ? 1 : 0, $transitionVariant->inventoryItem->fresh()->reserved_quantity);
            $this->assertSame(1, DB::table('notifications')->whereIn('event_key', ['reservation:'.$due->id.':confirmed', 'reservation:'.$due->id.':expired'])->count());
        } finally {
            DB::setDefaultConnection($original);
            DB::purge('mysql');
            config(['database.connections.mysql.database' => $originalMysqlDatabase, 'database.connections.mysql.url' => $originalMysqlUrl]);
        }
    }

    private function variant(string $sku): ProductVariant
    {
        $product = Product::factory()->create(['regular_price' => '10.00', 'sale_price' => null]);
        $variant = app(ProductVariantService::class)->createVariant($product, ['sku' => $sku, 'is_active' => true, 'price_adjustment' => '0.00']);
        app(InventoryService::class)->stockIn($variant, 1, 'seed-'.$sku);

        return $variant;
    }

    private function profile(): ResellerProfile
    {
        $user = User::factory()->create(['utype' => User::TYPE_RESELLER, 'is_active' => true]);

        return ResellerProfile::create(['user_id' => $user->id, 'status' => ResellerProfile::STATUS_ACTIVE, 'reservation_enabled' => true, 'reservation_timeout_minutes' => 30]);
    }

    private function race(array $operations, int|array $id, string $key): array
    {
        $barrier = tempnam(sys_get_temp_dir(), 'b2b-race-');
        unlink($barrier);
        $workers = [];
        try {
            foreach ($operations as $index => $operation) {
                $workerId = is_array($id) ? $id[$index] : $id;
                $worker = new Process([PHP_BINARY, base_path('tests/Support/reservation_concurrency_worker.php'), $operation, (string) $workerId, $key, $barrier, (string) $index], base_path(), [
                    'B2B_MYSQL_CONCURRENCY_DATABASE' => getenv('B2B_MYSQL_CONCURRENCY_DATABASE'),
                ]);
                $worker->setTimeout(30);
                $worker->start();
                $workers[] = $worker;
            }
            $deadline = microtime(true) + 15;
            while ((! file_exists($barrier.'.0.ready') || ! file_exists($barrier.'.1.ready')) && microtime(true) < $deadline) {
                usleep(10000);
            }
            $this->assertFileExists($barrier.'.0.ready');
            $this->assertFileExists($barrier.'.1.ready');
            touch($barrier);
            $results = [];
            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput());
                $results[] = json_decode($worker->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            }

            return $results;
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop();
                }
            }
            foreach ([$barrier, $barrier.'.0.ready', $barrier.'.1.ready'] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}
