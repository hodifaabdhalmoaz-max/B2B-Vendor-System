<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('product_variants')->orderBy('id')->chunkById(500, function ($variants): void {
            $now = now();
            $rows = $variants->map(fn ($variant) => [
                'product_variant_id' => $variant->id,
                'stock_on_hand' => 0,
                'reserved_quantity' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            // Unique product_variant_id keeps reruns safe and preserves existing balances.
            DB::table('inventory_items')->insertOrIgnore($rows);
        });
    }

    public function down(): void
    {
        // Structural zero rows may have gained stock since migration; never delete them.
    }
};
