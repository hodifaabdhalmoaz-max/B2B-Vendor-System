<?php

use App\Models\ProductVariant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check before changing the schema so a failed MySQL migration can be retried safely.
        $duplicates = DB::table('product_variants')
            ->select('product_id', 'color_id', 'size_id', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('product_id', 'color_id', 'size_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $details = $duplicates
                ->map(fn ($row) => 'product_id='.$row->product_id.', variant_key='.ProductVariant::buildVariantKey($row->color_id, $row->size_id).', count='.$row->duplicate_count)
                ->implode('; ');

            throw new RuntimeException("Duplicate product variant identities exist. Resolve before adding variant_key uniqueness: {$details}");
        }

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('variant_key', 64)
                ->default(ProductVariant::buildVariantKey(null, null))
                ->after('size_id');
        });

        DB::table('product_variants')
            ->select(['id', 'color_id', 'size_id'])
            ->orderBy('id')
            ->chunkById(200, function ($variants): void {
                foreach ($variants as $variant) {
                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update([
                            'variant_key' => ProductVariant::buildVariantKey(
                                $variant->color_id ? (int) $variant->color_id : null,
                                $variant->size_id ? (int) $variant->size_id : null
                            ),
                        ]);
                }
            });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique(['product_id', 'variant_key'], 'product_variants_product_variant_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique('product_variants_product_variant_key_unique');
            $table->dropColumn('variant_key');
        });
    }
};
