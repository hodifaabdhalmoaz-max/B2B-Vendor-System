<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: expand the 'mode' enum column to include all supported payment methods
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Change enum to string to support all payment modes
        DB::statement("ALTER TABLE `transactions` MODIFY `mode` VARCHAR(50) NOT NULL DEFAULT 'cod'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `transactions` MODIFY `mode` ENUM('cod','card','paypal') NOT NULL DEFAULT 'cod'");
    }
};
