<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexSafely('products', ['featured', 'created_at'], 'products_featured_created_idx');
        $this->addIndexSafely('products', ['stock_status', 'created_at'], 'products_stock_created_idx');
        $this->addIndexSafely('products', ['category_id', 'featured'], 'products_category_featured_idx');
        $this->addIndexSafely('products', ['brand_id', 'featured'], 'products_brand_featured_idx');
        $this->addIndexSafely('products', ['regular_price', 'sale_price'], 'products_price_idx');
        $this->addIndexSafely('products', 'views', 'products_views_idx');

        $this->addIndexSafely('orders', ['user_id', 'status'], 'orders_user_status_idx');
        $this->addIndexSafely('orders', ['status', 'created_at'], 'orders_status_created_idx');
        $this->addIndexSafely('orders', ['created_at', 'total'], 'orders_created_total_idx');

        $this->addIndexSafely('order_items', ['product_id', 'created_at'], 'order_items_product_created_idx');

        $this->addIndexSafely('reviews', ['product_id', 'status'], 'reviews_product_status_idx');
        $this->addIndexSafely('reviews', ['user_id', 'created_at'], 'reviews_user_created_idx');
        $this->addIndexSafely('reviews', ['rating', 'status'], 'reviews_rating_status_idx');

        $this->addIndexSafely('user_activities', ['user_id', 'created_at'], 'user_activities_user_created_idx');
        $this->addIndexSafely('user_activities', ['action', 'created_at'], 'user_activities_action_created_idx');
        $this->addIndexSafely('user_activities', 'ip_address', 'user_activities_ip_idx');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('products', 'products_featured_created_idx');
        $this->dropIndexSafely('products', 'products_stock_created_idx');
        $this->dropIndexSafely('products', 'products_category_featured_idx');
        $this->dropIndexSafely('products', 'products_brand_featured_idx');
        $this->dropIndexSafely('products', 'products_price_idx');
        $this->dropIndexSafely('products', 'products_views_idx');

        $this->dropIndexSafely('orders', 'orders_user_status_idx');
        $this->dropIndexSafely('orders', 'orders_status_created_idx');
        $this->dropIndexSafely('orders', 'orders_created_total_idx');

        $this->dropIndexSafely('order_items', 'order_items_product_created_idx');

        $this->dropIndexSafely('reviews', 'reviews_product_status_idx');
        $this->dropIndexSafely('reviews', 'reviews_user_created_idx');
        $this->dropIndexSafely('reviews', 'reviews_rating_status_idx');

        $this->dropIndexSafely('user_activities', 'user_activities_user_created_idx');
        $this->dropIndexSafely('user_activities', 'user_activities_action_created_idx');
        $this->dropIndexSafely('user_activities', 'user_activities_ip_idx');
    }

    private function addIndexSafely(string $table, array|string $columns, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->columnsExist($table, (array) $columns) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndexSafely(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    private function columnsExist(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = DB::connection();

        if ($connection->getDriverName() === 'sqlite') {
            return collect($connection->select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => ($index->name ?? null) === $indexName);
        }

        return ! empty($connection->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]));
    }
};
