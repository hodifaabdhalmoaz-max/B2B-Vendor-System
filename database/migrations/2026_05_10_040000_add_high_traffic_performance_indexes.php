<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing performance indexes for high-traffic queries.
 * These indexes target the most common query patterns in the store.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Products table — most queried table
        Schema::table('products', function (Blueprint $table) {
            // Composite index for featured + created_at (homepage query)
            $table->index(['featured', 'created_at'], 'idx_products_featured_created');
            
            // Composite index for category filtering + price sorting
            $table->index(['category_id', 'regular_price'], 'idx_products_category_price');
            
            // Composite index for brand filtering
            $table->index(['brand_id', 'created_at'], 'idx_products_brand_created');
            
            // Index for sale_price (used in price filter queries)
            $table->index('sale_price', 'idx_products_sale_price');
            
            // Index for stock status filtering
            $table->index('stock_status', 'idx_products_stock_status');
        });

        // Reviews table — frequent aggregate queries (COUNT, AVG)
        Schema::table('reviews', function (Blueprint $table) {
            // Composite index for product reviews with status filter
            $table->index(['product_id', 'status', 'rating'], 'idx_reviews_product_status_rating');
        });

        // Orders table — admin dashboard queries
        Schema::table('orders', function (Blueprint $table) {
            // Index for status filtering and date sorting
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
            
            // Index for user order history
            $table->index(['user_id', 'created_at'], 'idx_orders_user_created');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_featured_created');
            $table->dropIndex('idx_products_category_price');
            $table->dropIndex('idx_products_brand_created');
            $table->dropIndex('idx_products_sale_price');
            $table->dropIndex('idx_products_stock_status');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_product_status_rating');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status_created');
            $table->dropIndex('idx_orders_user_created');
        });
    }
};
