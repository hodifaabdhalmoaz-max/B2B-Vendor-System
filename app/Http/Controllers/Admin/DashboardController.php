<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Statistic;
use App\Models\User;
use App\Services\StatisticService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $statisticService;

    public function __construct(StatisticService $statisticService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->statisticService = $statisticService;
    }

    /**
     * Display admin dashboard.
     */
    public function index()
    {
        // Get sales statistics
        $totalSales = Order::where('status', 'delivered')->sum('total');
        $monthlySales = Order::where('status', 'delivered')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total');
        $dailySales = Order::where('status', 'delivered')
            ->whereDate('created_at', Carbon::today())
            ->sum('total');

        // Get order statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'ordered')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'canceled')->count();

        // Get product statistics
        $totalProducts = Product::count();
        $outOfStockProducts = Product::where('stock_status', 'outofstock')->count();

        // Get user statistics
        $totalUsers = User::where('utype', 'USR')->count();
        $newUsers = User::where('utype', 'USR')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Get recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Get top selling products
        $topSellingProducts = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);

                return [
                    'product' => $product,
                    'total_quantity' => $item->total_quantity,
                ];
            });

        // Get sales chart data
        $salesChartData = $this->getSalesChartData();

        // Log dashboard view
        $this->statisticService->log('admin_dashboard_view');

        return view('admin.dashboard', compact(
            'totalSales',
            'monthlySales',
            'dailySales',
            'totalOrders',
            'pendingOrders',
            'processingOrders',
            'shippedOrders',
            'deliveredOrders',
            'cancelledOrders',
            'totalProducts',
            'outOfStockProducts',
            'totalUsers',
            'newUsers',
            'recentOrders',
            'topSellingProducts',
            'salesChartData'
        ));
    }

    /**
     * Get sales chart data.
     */
    private function getSalesChartData()
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $salesData = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        // Fill in missing dates with zero sales
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();
            $labels[] = $date->format('M d');

            $sale = $salesData->firstWhere('date', $dateString);
            $data[] = $sale ? $sale->total_sales : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Display statistics page.
     */
    public function statistics()
    {
        // Get page view statistics
        $pageViews = Statistic::where('type', 'page_view')
            ->count();

        // Get product view statistics
        $productViews = Statistic::where('type', 'product_view')
            ->count();

        // Get search statistics
        $searches = Statistic::where('type', 'search')
            ->count();

        // Get cart add statistics
        $cartAdds = Statistic::where('type', 'cart_add')
            ->count();

        // Get wishlist add statistics
        $wishlistAdds = Statistic::where('type', 'wishlist_add')
            ->count();

        // Get checkout statistics
        $checkouts = Statistic::where('type', 'checkout')
            ->count();

        // Get payment statistics
        $payments = Statistic::where('type', 'payment')
            ->count();

        // Get top searched terms
        $topSearchTerms = Statistic::where('type', 'search')
            ->select('data')
            ->get()
            ->map(function ($item) {
                $data = json_decode($item->data, true);

                return $data['query'] ?? '';
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(10);

        // Get top viewed products
        $topViewedProducts = Statistic::where('type', 'product_view')
            ->select('reference_id', DB::raw('COUNT(*) as view_count'))
            ->groupBy('reference_id')
            ->orderByDesc('view_count')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->reference_id);

                return [
                    'product' => $product,
                    'view_count' => $item->view_count,
                ];
            });

        return view('admin.statistics', compact(
            'pageViews',
            'productViews',
            'searches',
            'cartAdds',
            'wishlistAdds',
            'checkouts',
            'payments',
            'topSearchTerms',
            'topViewedProducts'
        ));
    }
}
