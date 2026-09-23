<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Nullable because pre-Phase-4 movements cannot be reconstructed reliably.
            $table->bigInteger('stock_delta')->nullable();
            $table->bigInteger('reserved_delta')->nullable();
            $table->unsignedInteger('stock_on_hand_after')->nullable();
            $table->unsignedInteger('reserved_quantity_after')->nullable();
            $table->char('request_fingerprint', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['stock_delta', 'reserved_delta', 'stock_on_hand_after', 'reserved_quantity_after', 'request_fingerprint']);
        });
    }
};
