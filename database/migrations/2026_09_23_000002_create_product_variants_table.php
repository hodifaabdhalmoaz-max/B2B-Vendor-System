<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->decimal('price_adjustment', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['product_id', 'color_id', 'size_id'], 'product_variants_product_color_size_unique');
            $table->index(['product_id', 'is_active']);
            $table->index(['color_id', 'size_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
