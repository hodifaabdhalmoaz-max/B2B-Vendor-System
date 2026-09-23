<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Services\RevenueAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class RevenueAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected RevenueAnalyticsService $revenueService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revenueService = new RevenueAnalyticsService();
    }

    /** @test */
    public function it_can_calculate_revenue_analytics()
    {
        // إنشاء مستخدم تجريبي
        $user = User::factory()->create();

        // إنشاء طلبات تجريبية
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'total' => 100.00,
            'created_at' => Carbon::now()->subDays(2)
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'total' => 50.00,
            'created_at' => Carbon::now()->subDays(1)
        ]);

        // اختبار تحليل الإيرادات
        $analytics = $this->revenueService->getRevenueAnalytics('this_week');

        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('current', $analytics);
        $this->assertArrayHasKey('previous', $analytics);
        $this->assertArrayHasKey('changes', $analytics);
        $this->assertArrayHasKey('chart_data', $analytics);

        // التحقق من البيانات الحالية
        $this->assertEquals(150.00, $analytics['current']['revenue']);
        $this->assertEquals(2, $analytics['current']['orders_count']);
    }

    /** @test */
    public function it_can_get_overall_summary()
    {
        // إنشاء مستخدم تجريبي
        $user = User::factory()->create();

        // إنشاء طلبات بحالات مختلفة
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'total' => 100.00
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'ordered',
            'total' => 75.00
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'canceled',
            'total' => 25.00
        ]);

        $summary = $this->revenueService->getOverallSummary();

        $this->assertEquals(100.00, $summary['total_revenue']);
        $this->assertEquals(3, $summary['total_orders']);
        $this->assertEquals(1, $summary['total_delivered']);
        $this->assertEquals(1, $summary['total_pending']);
        $this->assertEquals(1, $summary['total_cancelled']);
    }

    /** @test */
    public function it_handles_empty_data_gracefully()
    {
        $analytics = $this->revenueService->getRevenueAnalytics('this_week');

        $this->assertIsArray($analytics);
        $this->assertEquals(0, $analytics['current']['revenue']);
        $this->assertEquals(0, $analytics['current']['orders_count']);
    }

    /** @test */
    public function it_calculates_percentage_changes_correctly()
    {
        $user = User::factory()->create();

        // طلبات الأسبوع الماضي
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'total' => 100.00,
            'created_at' => Carbon::now()->subWeek()->subDays(2)
        ]);

        // طلبات هذا الأسبوع
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'total' => 150.00,
            'created_at' => Carbon::now()->subDays(1)
        ]);

        $analytics = $this->revenueService->getRevenueAnalytics('this_week');

        // يجب أن تكون هناك زيادة 50%
        $this->assertEquals(50.00, $analytics['changes']['revenue']);
    }
}
