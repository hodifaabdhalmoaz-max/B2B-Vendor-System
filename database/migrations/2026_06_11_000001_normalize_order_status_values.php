<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE orders SET status = 'canceled' WHERE status = 'cancelled'");

        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY status ENUM('ordered', 'processing', 'shipped', 'delivered', 'canceled') NOT NULL DEFAULT 'ordered'");
    }

    public function down(): void
    {
        DB::statement("UPDATE orders SET status = 'ordered' WHERE status IN ('processing', 'shipped')");

        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY status ENUM('ordered', 'delivered', 'canceled') NOT NULL DEFAULT 'ordered'");
    }
};
