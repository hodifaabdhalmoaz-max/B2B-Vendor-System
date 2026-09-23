<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ErrorTrackingService;
use App\Services\MonitoringService;
use App\Services\StatisticService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsDashboardController extends Controller
{
    protected $monitoringService;

    protected $errorTrackingService;

    protected $statisticService;

    protected $orderRepository;

    protected $productRepository;

    protected $userRepository;

    public function __construct(
        MonitoringService $monitoringService,
        ErrorTrackingService $errorTrackingService,
        StatisticService $statisticService,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (! Auth::check() || Auth::user()->utype !== 'ADM') {
                abort(403, 'Unauthorized access');
            }

            return $next($request);
        });

        $this->monitoringService = $monitoringService;
        $this->errorTrackingService = $errorTrackingService;
        $this->statisticService = $statisticService;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display analytics dashboard
     */
    public function index()
    {
        $data = [
            'system_health' => $this->monitoringService->getSystemHealth(),
            'order_stats' => $this->orderRepository->getStatistics(),
            'user_stats' => $this->userRepository->getStatistics(),
            'error_stats' => $this->errorTrackingService->getErrorStatistics(7),
            'performance_metrics' => $this->monitoringService->getPerformanceMetrics(),
        ];

        return view('admin.analytics.dashboard', compact('data'));
    }

    /**
     * Get real-time system health
     */
    public function systemHealth(): JsonResponse
    {
        $health = $this->monitoringService->getSystemHealth();

        return response()->json($health);
    }

    /**
     * Get performance metrics
     */
    public function performanceMetrics(): JsonResponse
    {
        $metrics = $this->monitoringService->getPerformanceMetrics();

        return response()->json($metrics);
    }

    /**
     * Get error statistics
     */
    public function errorStatistics(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $stats = $this->errorTrackingService->getErrorStatistics($days);

        return response()->json($stats);
    }

    /**
     * Get sales analytics
     */
    public function salesAnalytics(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $data = [
            'monthly_sales' => $this->orderRepository->getMonthlySales($year),
            'top_products' => $this->productRepository->getTopSelling(10),
            'revenue_by_status' => $this->getRevenueByStatus(),
            'sales_trends' => $this->getSalesTrends($year),
        ];

        return response()->json($data);
    }

    /**
     * Get user analytics
     */
    public function userAnalytics(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $data = [
            'monthly_registrations' => $this->userRepository->getMonthlyRegistrations($year),
            'top_customers' => $this->userRepository->getTopCustomersBySpending(10),
            'user_activity' => $this->getUserActivityMetrics(),
            'user_demographics' => $this->getUserDemographics(),
        ];

        return response()->json($data);
    }

    /**
     * Get product analytics
     */
    public function productAnalytics(): JsonResponse
    {
        $data = [
            'top_selling' => $this->productRepository->getTopSelling(10),
            'low_stock' => $this->productRepository->getLowStock(10, 10),
            'out_of_stock' => $this->productRepository->getOutOfStock(10),
            'product_performance' => $this->getProductPerformanceMetrics(),
        ];

        return response()->json($data);
    }

    /**
     * Export analytics report
     */
    public function exportReport(Request $request)
    {
        $type = $request->get('type', 'sales');
        $format = $request->get('format', 'pdf');
        $period = $request->get('period', 'monthly');

        // This would generate and return the report
        // For now, return a simple response
        return response()->json([
            'message' => 'Report generation started',
            'type' => $type,
            'format' => $format,
            'period' => $period,
        ]);
    }

    /**
     * Get revenue breakdown by order status
     */
    protected function getRevenueByStatus(): array
    {
        return [
            'delivered' => $this->orderRepository->getByStatus('delivered')->sum('total'),
            'pending' => $this->orderRepository->getByStatus('ordered')->sum('total'),
            'processing' => $this->orderRepository->getByStatus('processing')->sum('total'),
            'shipped' => $this->orderRepository->getByStatus('shipped')->sum('total'),
            'cancelled' => $this->orderRepository->getByStatus('canceled')->sum('total'),
        ];
    }

    /**
     * Get sales trends for the year
     */
    protected function getSalesTrends(int $year): array
    {
        // This would calculate growth rates, seasonal trends, etc.
        return [
            'growth_rate' => 15.5, // Mock data
            'seasonal_peak' => 'December',
            'best_performing_month' => 'November',
            'average_order_value' => 250.75,
        ];
    }

    /**
     * Get user activity metrics
     */
    protected function getUserActivityMetrics(): array
    {
        // This would analyze user login patterns, session duration, etc.
        return [
            'daily_active_users' => 150, // Mock data
            'weekly_active_users' => 800,
            'monthly_active_users' => 2500,
            'average_session_duration' => '12:35',
        ];
    }

    /**
     * Get user demographics
     */
    protected function getUserDemographics(): array
    {
        // This would analyze user locations, age groups, etc.
        return [
            'by_location' => [
                'Sanaa' => 35,
                'Aden' => 25,
                'Taiz' => 20,
                'Hodeidah' => 15,
                'Other' => 5,
            ],
            'by_type' => [
                'customers' => 95,
                'admins' => 5,
            ],
        ];
    }

    /**
     * Get product performance metrics
     */
    protected function getProductPerformanceMetrics(): array
    {
        return [
            'total_products' => $this->productRepository->count(),
            'active_products' => $this->productRepository->getByStockStatus('instock')->count(),
            'featured_products' => $this->productRepository->getFeatured()->count(),
            'average_rating' => 4.2, // Mock data
            'total_reviews' => 1250, // Mock data
        ];
    }
}
