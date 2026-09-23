<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ad_placements')) {
            return;
        }

        $now = now();

        DB::table('ad_placements')->upsert([
            [
                'key' => 'messages_activity',
                'name' => 'Message center activity',
                'recommended_size' => 'mobile card 420x140',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'messages_promo',
                'name' => 'Message center promo',
                'recommended_size' => 'mobile coupon/card 420x180',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'messages_news',
                'name' => 'Message center news',
                'recommended_size' => 'mobile card 420x140',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['key'], ['name', 'recommended_size', 'is_active', 'updated_at']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('ad_placements')) {
            return;
        }

        DB::table('ad_placements')
            ->whereIn('key', ['messages_activity', 'messages_promo', 'messages_news'])
            ->delete();
    }
};
