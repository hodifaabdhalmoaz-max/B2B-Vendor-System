<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('stock_on_hand')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->nullable();
            $table->timestamps();

            $table->index(['stock_on_hand', 'reserved_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
