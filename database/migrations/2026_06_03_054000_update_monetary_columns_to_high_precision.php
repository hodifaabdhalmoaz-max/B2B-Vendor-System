<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update products table
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('regular_price', 12, 2)->change();
            $table->decimal('sale_price', 12, 2)->nullable()->change();
        });

        // Update orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->change();
            $table->decimal('discount', 12, 2)->default(0)->change();
            $table->decimal('tax', 12, 2)->change();
            $table->decimal('total', 12, 2)->change();
        });

        // Update order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
        });

        // Update coupons table
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('value', 12, 2)->change();
            $table->decimal('cart_value', 12, 2)->change();
        });

        // Update product_attributes table
        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                $table->decimal('price_adjustment', 12, 2)->default(0.00)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert products table
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('regular_price', 8, 2)->change();
            $table->decimal('sale_price', 8, 2)->nullable()->change();
        });

        // Revert orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 8, 2)->change();
            $table->decimal('discount', 8, 2)->default(0)->change();
            $table->decimal('tax', 8, 2)->change();
            $table->decimal('total', 8, 2)->change();
        });

        // Revert order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
        });

        // Revert coupons table
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('value', 8, 2)->change();
            $table->decimal('cart_value', 8, 2)->change();
        });

        // Revert product_attributes table
        if (Schema::hasTable('product_attributes')) {
            Schema::table('product_attributes', function (Blueprint $table) {
                $table->decimal('price_adjustment', 10, 2)->default(0.00)->change();
            });
        }
    }
};
