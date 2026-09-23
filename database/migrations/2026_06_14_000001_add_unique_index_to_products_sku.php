<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $duplicates = DB::table('products')
            ->select('SKU', DB::raw('COUNT(*) as total'))
            ->groupBy('SKU')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $products = DB::table('products')
                ->where('SKU', $duplicate->SKU)
                ->orderBy('id')
                ->get(['id', 'SKU']);

            foreach ($products->skip(1) as $product) {
                $baseSku = trim((string) $product->SKU) !== ''
                    ? mb_substr((string) $product->SKU, 0, 240, 'UTF-8')
                    : 'PRODUCT';

                $candidate = $baseSku.'-'.$product->id;
                $counter = 2;

                while (
                    DB::table('products')
                        ->where('SKU', $candidate)
                        ->where('id', '<>', $product->id)
                        ->exists()
                ) {
                    $candidate = $baseSku.'-'.$product->id.'-'.$counter;
                    $counter++;
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['SKU' => $candidate]);
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique('SKU', 'products_sku_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
        });
    }
};
