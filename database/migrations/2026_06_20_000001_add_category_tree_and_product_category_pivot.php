<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! $this->indexExists('categories', 'categories_parent_id_idx')) {
                    $table->index('parent_id', 'categories_parent_id_idx');
                }
            });
        }

        if (! Schema::hasTable('category_product')) {
            Schema::create('category_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['category_id', 'product_id'], 'category_product_unique');
                $table->index('product_id', 'category_product_product_id_idx');
            });
        }

        if (Schema::hasTable('products') && Schema::hasTable('category_product')) {
            DB::table('products')
                ->whereNotNull('category_id')
                ->select(['id', 'category_id'])
                ->orderBy('id')
                ->chunkById(500, function ($products) {
                    $now = now();
                    $rows = $products->map(fn ($product) => [
                        'category_id' => (int) $product->category_id,
                        'product_id' => (int) $product->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('category_product')->upsert(
                        $rows,
                        ['category_id', 'product_id'],
                        ['updated_at']
                    );
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');

        if (Schema::hasTable('categories') && $this->indexExists('categories', 'categories_parent_id_idx')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_parent_id_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        $connection = DB::connection();

        if ($connection->getDriverName() === 'sqlite') {
            return collect($connection->select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => ($index->name ?? null) === $indexName);
        }

        return ! empty($connection->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]));
    }
};
