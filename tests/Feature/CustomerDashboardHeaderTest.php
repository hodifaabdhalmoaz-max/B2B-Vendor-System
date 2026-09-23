<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDashboardHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_dashboard_uses_compact_header_and_profile_summary(): void
    {
        $user = User::factory()->create(['name' => 'Hodifa Customer']);

        $response = $this->actingAs($user)->get(route('user.index'));

        $response->assertOk();
        $response->assertSee('customer-page-header', false);
        $response->assertSee('<div class="customer-page-profile">', false);
        $response->assertSee('Hodifa Customer');
        $response->assertDontSee('dashboard-hero', false);
    }

    public function test_customer_child_pages_use_compact_header_without_dashboard_profile_summary(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.orders'));

        $response->assertOk();
        $response->assertSee('customer-page-header', false);
        $response->assertSee('طلباتي');
        $response->assertDontSee('<div class="customer-page-profile">', false);
        $response->assertDontSee('dashboard-hero', false);
    }
}
