<?php

namespace Tests\Feature;

use App\Models\AdPlacement;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\User;
use App\Services\Marketing\PromotionResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketingPromotionResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_resolver_returns_only_active_matching_home_tab_campaigns_ordered_by_priority(): void
    {
        $placement = AdPlacement::firstOrCreate(['key' => 'home_tab_hero'], [
            'key' => 'home_tab_hero',
            'name' => 'Home tab hero',
            'is_active' => true,
        ]);

        $lowPriority = $this->campaignForTab($placement, 'kids', priority: 5, title: 'Kids low priority');
        $highPriority = $this->campaignForTab($placement, 'kids', priority: 20, title: 'Kids high priority');
        $this->campaignForTab($placement, 'gifts', priority: 50, title: 'Gifts campaign');
        $this->campaignForTab($placement, 'kids', priority: 100, title: 'Paused campaign', status: 'paused');
        $this->campaignForTab($placement, 'kids', priority: 100, title: 'Future campaign', startsAt: now()->addDay());
        $this->campaignForTab($placement, 'kids', priority: 100, title: 'Expired campaign', endsAt: now()->subDay());

        $resolved = app(PromotionResolver::class)->getForPlacement('home_tab_hero', [
            'home_tab_slug' => 'kids',
            'device' => 'mobile',
        ]);

        $this->assertTrue($resolved->first()->is($highPriority));
        $this->assertTrue($resolved->last()->is($lowPriority));
        $this->assertSame(['Kids high priority', 'Kids low priority'], $resolved->pluck('creative.title')->all());
    }

    public function test_home_page_renders_campaign_hero_assets_for_selected_category_tab(): void
    {
        $category = Category::factory()->create([
            'name' => 'Children',
            'slug' => 'children',
            'status' => 'active',
        ]);

        $placement = AdPlacement::firstOrCreate(['key' => 'home_tab_hero'], [
            'key' => 'home_tab_hero',
            'name' => 'Home tab hero',
            'is_active' => true,
        ]);

        $campaign = $this->campaignForTab($placement, 'kids', title: 'وفر أكثر على منتجات الأطفال');
        $campaign->creative()->update([
            'subtitle' => '#عالم_دنيا_الأطفال',
            'description' => 'أفضل منتجات الأطفال',
            'cta_text' => 'تسوق الآن',
            'category_id' => $category->id,
            'link_type' => 'category',
        ]);
        $campaign->assets()->createMany([
            [
                'role' => 'hero_product',
                'image_path' => 'campaigns/shoe.webp',
                'price_text' => '29 ر.ي',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'role' => 'hero_product',
                'image_path' => 'campaigns/socks.webp',
                'price_text' => '12 ر.ي',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ]);

        $response = $this->get(route('home.index', ['market_tab' => 'kids']));

        $response->assertOk();
        $response->assertSee('وفر أكثر على منتجات الأطفال');
        $response->assertSee('29 ر.ي');
        $response->assertSee('12 ر.ي');
    }

    public function test_home_page_renders_multiple_hero_campaigns_as_priority_ordered_slider(): void
    {
        $placement = AdPlacement::firstOrCreate(['key' => 'home_tab_hero'], [
            'key' => 'home_tab_hero',
            'name' => 'Home tab hero',
            'is_active' => true,
        ]);

        $low = $this->campaignForTab($placement, 'kids', priority: 10, title: 'Low priority campaign');
        $high = $this->campaignForTab($placement, 'kids', priority: 90, title: 'High priority campaign');

        $low->assets()->create([
            'role' => 'mobile_banner',
            'image_path' => 'campaigns/low.png',
            'is_active' => true,
        ]);
        $high->assets()->create([
            'role' => 'mobile_banner',
            'image_path' => 'campaigns/high.png',
            'is_active' => true,
        ]);

        $response = $this->get(route('home.index', ['market_tab' => 'kids']));

        $response->assertOk();
        $response->assertSee('data-hero-slider', false);
        $response->assertSee('data-hero-slide', false);
        $response->assertSeeInOrder(['High priority campaign', 'Low priority campaign']);
    }

    public function test_admin_can_open_create_campaign_page_with_form_options(): void
    {
        $admin = User::factory()->create(['utype' => 'ADM']);

        $response = $this->actingAs($admin)->get(route('admin.marketing.campaigns.create'));

        $response->assertOk();
        $response->assertSee('home_tab_hero');
        $response->assertSee('home_tab_banner');
        $response->assertSee('home_tab');
        $response->assertSee('url');
    }

    public function test_home_page_ignores_dynamic_category_slug_as_horizontal_tab(): void
    {
        Category::factory()->create([
            'name' => 'ملابس الأطفال',
            'slug' => 'ملابس-الأطفال',
            'status' => 'active',
            'show_in_home_tabs' => true,
            'home_tab_order' => 1,
        ]);

        $response = $this->get(route('home.index', ['market_tab' => 'ملابس-الأطفال']));

        $response->assertOk();
        $response->assertViewHas('activeMarketCategory', 'all');
    }

    public function test_admin_campaign_index_shows_edit_button_next_to_delete_button(): void
    {
        $admin = User::factory()->create(['utype' => 'ADM']);
        $placement = AdPlacement::firstOrCreate(['key' => 'home_tab_hero'], [
            'key' => 'home_tab_hero',
            'name' => 'Home tab hero',
            'is_active' => true,
        ]);
        $campaign = $this->campaignForTab($placement, 'children', title: 'Editable campaign');

        $response = $this->actingAs($admin)->get(route('admin.marketing.campaigns.index'));

        $response->assertOk();
        $response->assertSee('Editable campaign');
        $response->assertSee('تعديل');
        $response->assertSee(route('admin.marketing.campaigns.edit', $campaign), false);
        $response->assertSee('حذف');
    }

    public function test_campaign_asset_image_route_serves_public_disk_file_when_storage_link_is_missing(): void
    {
        Storage::disk('public')->put('campaigns/test-campaign-image.png', 'fake-image');

        $asset = Campaign::create([
            'name' => 'Image campaign',
            'slug' => 'image-campaign',
            'type' => 'home_tab_banner',
            'status' => 'active',
            'priority' => 0,
            'is_active' => true,
        ])->assets()->create([
            'role' => 'hero_product',
            'image_path' => 'campaigns/test-campaign-image.png',
            'is_active' => true,
        ]);

        $this->assertStringContainsString(route('campaign-assets.image', $asset), $asset->imageUrl());
        $this->get($asset->imageUrl())->assertOk();
    }

    private function campaignForTab(
        AdPlacement $placement,
        string $tabSlug,
        int $priority = 0,
        string $title = 'Campaign',
        string $status = 'active',
        mixed $startsAt = null,
        mixed $endsAt = null
    ): Campaign {
        $campaign = Campaign::create([
            'name' => $title,
            'slug' => str($title)->slug('-').'-'.uniqid(),
            'type' => 'home_tab_banner',
            'status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'priority' => $priority,
            'is_active' => true,
        ]);

        $campaign->creative()->create([
            'title' => $title,
            'link_type' => 'url',
            'link_url' => route('shop.index'),
        ]);
        $campaign->rules()->create([
            'target_type' => 'home_tab',
            'operator' => 'equals',
            'value' => ['tab_slug' => $tabSlug],
        ]);
        $campaign->placements()->attach($placement->id, ['sort_order' => 0]);

        return $campaign;
    }
}
