<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Read-only runtime contract; never repairs drift or runs migrations. */
class VerifyB2BSchema extends Command
{
    protected $signature = 'b2b:verify-schema';

    protected $description = 'Verify required B2B schema, constraints and migration history without changing data';

    public const COLUMNS = [
        'users' => 'id name username mobile password utype is_active force_password_change',
        'reseller_profiles' => 'id user_id business_name whatsapp governorate group_name notes status reservation_enabled reservation_timeout_minutes created_at updated_at',
        'products' => 'id name slug SKU regular_price sale_price',
        'product_variants' => 'id product_id color_id size_id sku variant_key price_adjustment is_active created_at updated_at',
        'inventory_items' => 'id product_variant_id stock_on_hand reserved_quantity low_stock_threshold',
        'inventory_movements' => 'id product_variant_id type quantity stock_delta reserved_delta stock_on_hand_after reserved_quantity_after reference_type reference_id actor_user_id idempotency_key request_fingerprint metadata created_at',
        'reservations' => 'id reservation_number reseller_profile_id idempotency_key request_fingerprint status expires_at confirmed_at cancelled_at released_at notes created_at updated_at',
        'reservation_items' => 'id reservation_id product_variant_id quantity unit_price product_name_snapshot sku_snapshot variant_snapshot created_at updated_at',
        'notifications' => 'id type notifiable_type notifiable_id data read_at event_key created_at updated_at',
        'wishlists' => 'id user_id product_id',
        'migrations' => 'id migration batch',
    ];

    public const UNIQUE_KEYS = [
        'reseller_profiles' => [['user_id']],
        'product_variants' => [['sku'], ['product_id', 'variant_key']],
        'inventory_items' => [['product_variant_id']],
        'inventory_movements' => [['idempotency_key']],
        'reservations' => [['reservation_number'], ['idempotency_key']],
        'reservation_items' => [['reservation_id', 'product_variant_id']],
        'notifications' => [['event_key']],
        'wishlists' => [['user_id', 'product_id']],
    ];

    public const MONETARY_TYPES = [
        // The original products migration uses Laravel's decimal(8,2) default.
        'products' => ['regular_price' => 'decimal(8,2)', 'sale_price' => 'decimal(8,2)'],
        'product_variants' => ['price_adjustment' => 'decimal(12,2)'],
        'reservation_items' => ['unit_price' => 'decimal(12,2)'],
    ];

    public const FOREIGN_KEYS = [
        'reseller_profiles' => ['user_id' => 'users'],
        'product_variants' => ['product_id' => 'products'],
        'inventory_items' => ['product_variant_id' => 'product_variants'],
        'inventory_movements' => ['product_variant_id' => 'product_variants'],
        'reservations' => ['reseller_profile_id' => 'reseller_profiles'],
        'reservation_items' => ['reservation_id' => 'reservations', 'product_variant_id' => 'product_variants'],
    ];

    public function handle(): int
    {
        $valid = true;
        $check = function (bool $ok, string $label, string $failure = 'MISSING') use (&$valid): void {
            $this->line('['.($ok ? 'OK' : $failure).'] '.$label);
            $valid = $valid && $ok;
        };

        try {
            $connection = DB::connection();
            $schema = $connection->getSchemaBuilder();
            if (! in_array($connection->getDriverName(), ['sqlite', 'mysql'], true)) {
                $this->error('Unsupported verification driver; use SQLite or MySQL.');

                return self::FAILURE;
            }
            foreach (self::COLUMNS as $table => $required) {
                $exists = $schema->hasTable($table);
                $check($exists, $table);
                if (! $exists) {
                    continue;
                }
                $metadata = collect($schema->getColumns($table))->keyBy('name');
                $columns = $metadata->keys()->all();
                foreach (explode(' ', $required) as $column) {
                    $check(in_array($column, $columns, true), "$table.$column");
                }
                $indexes = $schema->getIndexes($table);
                // Laravel's normalized SQLite index metadata omits partial predicates.
                $partialIndexes = $connection->getDriverName() === 'sqlite'
                    ? collect($connection->select('SELECT name FROM pragma_index_list(?) WHERE partial = 1', [$connection->getTablePrefix().$table]))->pluck('name')->all()
                    : [];
                foreach (self::UNIQUE_KEYS[$table] ?? [] as $key) {
                    $found = collect($indexes)->contains(fn ($index) => $index['unique'] && $index['columns'] === $key && ! in_array($index['name'], $partialIndexes, true));
                    $check($found, $table.'.'.implode('+', $key).' unique', 'MISSING INDEX');
                }
                foreach (self::MONETARY_TYPES[$table] ?? [] as $column => $expectedType) {
                    $type = $metadata->get($column)['type'] ?? '';
                    $check($connection->getDriverName() === 'sqlite' ? $type === 'numeric' : $type === $expectedType, "$table.$column monetary type", 'INCOMPATIBLE TYPE');
                }
                if ($table === 'inventory_movements') {
                    foreach (['stock_delta', 'reserved_delta'] as $column) {
                        $type = $metadata->get($column)['type'] ?? '';
                        $check($connection->getDriverName() === 'sqlite' ? $type === 'integer' : (bool) preg_match('/^bigint(?:\(\d+\))?$/', $type), "$table.$column signed delta", 'INCOMPATIBLE TYPE');
                    }
                }
                $foreignKeys = $schema->getForeignKeys($table);
                foreach (self::FOREIGN_KEYS[$table] ?? [] as $column => $parent) {
                    $found = collect($foreignKeys)->contains(fn ($fk) => $fk['columns'] === [$column]
                        && $fk['foreign_table'] === $parent && $fk['foreign_columns'] === ['id']
                        && in_array(strtolower($fk['on_delete']), ['restrict', 'no action'], true));
                    $check($found, "$table.$column -> $parent.id (restrict delete)", 'INCOMPATIBLE FK');
                }
            }
            // Compare canonical Phase 1–8 history with the runtime migration ledger.
            if ($schema->hasColumns('migrations', ['migration', 'batch'])) {
                $ran = $connection->table('migrations')->pluck('migration')->all();
                foreach (glob(database_path('migrations/2026_09_*.php')) as $path) {
                    $migration = basename($path, '.php');
                    $check(in_array($migration, $ran, true), "migration $migration", 'PENDING MIGRATION');
                }
            }
        } catch (Throwable) {
            // Connection/query exceptions can contain credentials or raw SQL.
            $this->error('[UNVERIFIED] Database inspection failed. Check connectivity and permissions; no repair was attempted.');

            return self::FAILURE;
        }

        if (! $valid) {
            $this->error('Schema drift or pending migrations detected. Run php artisan migrate:status. Apply pending canonical migrations with php artisan migrate; if already recorded, investigate drift and restore/rebuild only an explicitly disposable database.');
            $this->line('Movement balances: 2026_09_24_000001_add_inventory_movement_balances; notification event key: 2026_09_26_000001_add_reservation_event_key_to_notifications.');
        }

        return $valid ? self::SUCCESS : self::FAILURE;
    }
}
