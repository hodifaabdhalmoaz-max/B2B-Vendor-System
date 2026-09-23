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
        Schema::table('orders', function (Blueprint $table) {
            // Make delivered_date and canceled_date nullable
            $table->date('delivered_date')->nullable()->change();
            $table->date('canceled_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert back to non-nullable (this might fail if there are null values)
            $table->date('delivered_date')->nullable(false)->change();
            $table->date('canceled_date')->nullable(false)->change();
        });
    }
};
