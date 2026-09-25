<?php

// Separate PHP process, connection, and transaction for the opt-in MySQL gate.
require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Services\InventoryService;
use App\Services\ReservationService;
use Illuminate\Support\Facades\DB;

$database = getenv('B2B_MYSQL_CONCURRENCY_DATABASE') ?: '';
if (getenv('B2B_MYSQL_CONCURRENCY_ALLOW_FRESH') !== '1' || (! str_ends_with($database, '_test') && ! str_ends_with($database, '_testing'))) {
    fwrite(STDERR, "Explicit disposable database opt-in required.\n");
    exit(2);
}
config(['database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
DB::purge('mysql');
DB::setDefaultConnection('mysql');

[$script, $operation, $id, $key, $barrier, $index] = $argv;
file_put_contents($barrier.'.'.$index.'.ready', 'ready');
$deadline = microtime(true) + 15;
while (! file_exists($barrier)) {
    if (microtime(true) > $deadline) {
        fwrite(STDERR, "Barrier timeout\n");
        exit(2);
    }
    usleep(10000);
}

try {
    if ($operation === 'reserve') {
        app(InventoryService::class)->reserve(ProductVariant::findOrFail((int) $id), 1, $key.'-'.$index);
        $result = ['ok' => true];
    } elseif ($operation === 'create' || $operation === 'create_same_key') {
        $reservation = app(ReservationService::class)->create(
            ResellerProfile::findOrFail((int) $id),
            [['product_variant_id' => (int) $key, 'quantity' => 1]],
            $operation === 'create_same_key' ? 'mysql-create-shared' : 'mysql-create-'.$index
        );
        $result = ['ok' => true, 'reservation_id' => $reservation->id];
    } elseif ($operation === 'confirm') {
        app(ReservationService::class)->confirm(Reservation::findOrFail((int) $id));
        $result = ['ok' => true];
    } elseif ($operation === 'expire') {
        $result = ['ok' => app(ReservationService::class)->expire(Reservation::findOrFail((int) $id))];
    } else {
        throw new InvalidArgumentException('Unknown worker operation.');
    }
} catch (Throwable $exception) {
    $result = ['ok' => false, 'error' => get_class($exception)];
}

echo json_encode($result, JSON_THROW_ON_ERROR);
