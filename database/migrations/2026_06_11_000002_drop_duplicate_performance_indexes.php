<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropIndexIfExists('products', 'idx_products_featured_created');
        $this->dropIndexIfExists('orders', 'idx_orders_status_created');
    }

    public function down(): void
    {
        $this->addIndexIfMissing('products', 'idx_products_featured_created', ['featured', 'created_at']);
        $this->addIndexIfMissing('orders', 'idx_orders_status_created', ['status', 'created_at']);
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if ($this->indexExists($table, $index)) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement("DROP INDEX {$index}");

                return;
            }

            DB::statement("ALTER TABLE {$table} DROP INDEX {$index}");
        }
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! $this->indexExists($table, $index)) {
            $columnList = implode(', ', $columns);

            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement("CREATE INDEX {$index} ON {$table} ({$columnList})");

                return;
            }

            DB::statement("ALTER TABLE {$table} ADD INDEX {$index} ({$columnList})");
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = DB::connection();

        if ($connection->getDriverName() === 'sqlite') {
            return collect($connection->select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($existingIndex) => ($existingIndex->name ?? null) === $index);
        }

        return ! empty($connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]));
    }
};
