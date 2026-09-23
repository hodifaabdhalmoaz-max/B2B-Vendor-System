<?php

namespace Tests\Feature;

use App\Models\AdPlacement;
use App\Models\Campaign;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_center_renders_shortcuts_and_admin_selected_recommendations(): void
    {
        Product::factory()->create([
            'name' => 'Recommended baby set',
            'slug' => 'recommended-baby-set',
            'stock_status' => 'instock',
            'storefront_sections' => ['messages.recommended'],
            'storefront_order' => 1,
        ]);

        $response = $this->get(route('messages.index'));

        $response->assertOk();
        $response->assertSee('الرسائل');
        $response->assertSee('الطلبات');
        $response->assertSee('النشاط');
        $response->assertSee('العروض');
        $response->assertSee('الأخبار');
        $response->assertSee('أشياء قد تعجبك');
        $response->assertSee('Recommended baby set');
    }

    public function test_promo_section_uses_active_campaigns_and_clear_hides_current_messages(): void
    {
        $placement = AdPlacement::firstOrCreate(['key' => 'messages_promo'], [
            'name' => 'Message center promo',
            'is_active' => true,
        ]);
        $campaign = Campaign::create([
            'name' => 'Coupon reminder',
            'slug' => 'coupon-reminder',
            'type' => 'coupon',
            'status' => 'active',
            'priority' => 10,
            'is_active' => true,
        ]);

        $campaign->creative()->create([
            'title' => 'قسيمتك على وشك الانتهاء',
            'description' => 'استخدم القسيمة قبل انتهاء الوقت.',
            'cta_text' => '20% OFF',
            'link_type' => 'url',
            'link_url' => route('shop.index'),
        ]);
        $campaign->placements()->attach($placement->id, ['sort_order' => 0]);

        $this->get(route('messages.section', 'promo'))
            ->assertOk()
            ->assertSee('قسيمتك على وشك الانتهاء');

        $this->post(route('messages.clear'))->assertRedirect();

        $this->get(route('messages.section', 'promo'))
            ->assertOk()
            ->assertDontSee('قسيمتك على وشك الانتهاء')
            ->assertSee('لا توجد عروض حالية');
    }
}
