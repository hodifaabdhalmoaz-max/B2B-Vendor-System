<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'show_in_home_tabs')) {
                $table->boolean('show_in_home_tabs')->default(false)->after('storefront_order');
            }

            if (! Schema::hasColumn('categories', 'home_tab_order')) {
                $table->unsignedSmallInteger('home_tab_order')->default(0)->after('show_in_home_tabs');
            }

            if (! Schema::hasColumn('categories', 'home_tab_label')) {
                $table->string('home_tab_label')->nullable()->after('home_tab_order');
            }

            if (! Schema::hasColumn('categories', 'icon')) {
                $table->string('icon')->nullable()->after('image');
            }
        });

        if (! $this->indexExists('categories', 'categories_home_tabs_idx')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(['show_in_home_tabs', 'home_tab_order'], 'categories_home_tabs_idx');
            });
        }

        Schema::create('ad_placements', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('recommended_size')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('banner');
            $table->string('status')->default('draft')->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->integer('priority')->default(0)->index();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();

            $table->index(['is_active', 'status', 'priority'], 'campaigns_active_status_priority_idx');
        });

        Schema::create('campaign_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('link_type')->default('url');
            $table->string('link_url')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('background_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('alt_text')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('role')->index();
            $table->string('image_path');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('price_text')->nullable();
            $table->string('link_url')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['campaign_id', 'role', 'sort_order'], 'campaign_assets_campaign_role_order_idx');
        });

        Schema::create('campaign_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('target_type')->index();
            $table->string('operator')->default('equals');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'target_type'], 'campaign_rules_campaign_target_idx');
        });

        Schema::create('campaign_placement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ad_placement_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['campaign_id', 'ad_placement_id'], 'campaign_placement_unique');
            $table->index(['ad_placement_id', 'sort_order'], 'campaign_placement_ad_order_idx');
        });

        $this->seedPlacements();
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_placement');
        Schema::dropIfExists('campaign_rules');
        Schema::dropIfExists('campaign_assets');
        Schema::dropIfExists('campaign_creatives');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('ad_placements');

        if (Schema::hasTable('categories') && $this->indexExists('categories', 'categories_home_tabs_idx')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_home_tabs_idx');
            });
        }

        Schema::table('categories', function (Blueprint $table) {
            foreach (['icon', 'home_tab_label', 'home_tab_order', 'show_in_home_tabs'] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function seedPlacements(): void
    {
        $now = now();

        DB::table('ad_placements')->upsert([
            ['key' => 'home_tab_hero', 'name' => 'Home tab hero', 'recommended_size' => 'mobile 390x270', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'home_tab_offer_strip', 'name' => 'Home tab offer strip', 'recommended_size' => 'mobile 390x70', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'home_tab_promo_cards', 'name' => 'Home tab promo cards', 'recommended_size' => '3 cards x 120x120', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'home_tab_circle_categories', 'name' => 'Home tab circle categories', 'recommended_size' => 'circle 96x96', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'home_tab_bottom_banner', 'name' => 'Home tab bottom banner', 'recommended_size' => 'mobile 390x120', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'home_hero', 'name' => 'Home hero', 'recommended_size' => 'desktop hero', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'category_top_banner', 'name' => 'Category top banner', 'recommended_size' => 'responsive banner', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['key'], ['name', 'recommended_size', 'is_active', 'updated_at']);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        $connection = DB::connection();

        if ($connection->getDriverName() === 'sqlite') {
            return collect($connection->select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => ($index->name ?? null) === $indexName);
        }

        return ! empty($connection->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]));
    }
};
