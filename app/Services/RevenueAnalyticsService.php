<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevenueAnalyticsService
{
    /**
     * الحصول على تحليل الإيرادات للفترة المحددة
     */
    public function getRevenueAnalytics(string $period = 'this_week'): array
    {
        try {
            $dateRange = $this->getDateRange($period);
            $previousDateRange = $this->getPreviousDateRange($period);

            // البيانات الحالية
            $currentData = $this->calculatePeriodData($dateRange);
            
            // البيانات السابقة للمقارنة
            $previousData = $this->calculatePeriodData($previousDateRange);

            // حساب نسب التغيير
            $revenueChange = $this->calculatePercentageChange(
                $previousData['revenue'], 
                $currentData['revenue']
            );
            
            $ordersChange = $this->calculatePercentageChange(
                $previousData['orders_count'], 
                $currentData['orders_count']
            );

            return [
                'current' => $currentData,
                'previous' => $previousData,
                'changes' => [
                    'revenue' => $revenueChange,
                    'orders' => $ordersChange,
                ],
                'period' => $period,
                'date_range' => $dateRange,
                'chart_data' => $this->getChartData($period)
            ];

        } catch (\Exception $e) {
            Log::error('خطأ في تحليل الإيرادات: ' . $e->getMessage());
            return $this->getEmptyAnalytics();
        }
    }

    /**
     * حساب بيانات فترة محددة
     */
    private function calculatePeriodData(array $dateRange): array
    {
        // إجمالي الإيرادات (الطلبات المسلمة فقط)
        $revenue = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('total') ?? 0;

        // إجمالي قيمة الطلبات (جميع الطلبات)
        $totalOrders = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('total') ?? 0;

        // عدد الطلبات
        $ordersCount = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        // عدد الطلبات المسلمة
        $deliveredCount = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        // متوسط قيمة الطلب
        $averageOrderValue = $ordersCount > 0 ? $totalOrders / $ordersCount : 0;

        return [
            'revenue' => floatval($revenue),
            'total_orders' => floatval($totalOrders),
            'orders_count' => $ordersCount,
            'delivered_count' => $deliveredCount,
            'average_order_value' => round($averageOrderValue, 2)
        ];
    }

    /**
     * تحديد النطاق الزمني
     */
    private function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match($period) {
            'this_week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek()
            ],
            'last_week' => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek()
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth()
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth()
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear()
            ],
            'last_year' => [
                'start' => $now->copy()->subYear()->startOfYear(),
                'end' => $now->copy()->subYear()->endOfYear()
            ],
            default => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek()
            ]
        };
    }

    /**
     * تحديد النطاق الزمني السابق للمقارنة
     */
    private function getPreviousDateRange(string $period): array
    {
        $now = Carbon::now();

        return match($period) {
            'this_week' => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek()
            ],
            'last_week' => [
                'start' => $now->copy()->subWeeks(2)->startOfWeek(),
                'end' => $now->copy()->subWeeks(2)->endOfWeek()
            ],
            'this_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth()
            ],
            'last_month' => [
                'start' => $now->copy()->subMonths(2)->startOfMonth(),
                'end' => $now->copy()->subMonths(2)->endOfMonth()
            ],
            'this_year' => [
                'start' => $now->copy()->subYear()->startOfYear(),
                'end' => $now->copy()->subYear()->endOfYear()
            ],
            'last_year' => [
                'start' => $now->copy()->subYears(2)->startOfYear(),
                'end' => $now->copy()->subYears(2)->endOfYear()
            ],
            default => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek()
            ]
        };
    }

    /**
     * حساب نسبة التغيير
     */
    private function calculatePercentageChange(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * الحصول على بيانات الرسم البياني
     */
    private function getChartData(string $period): array
    {
        Carbon::setLocale('ar');

        $chartData = [
            'labels' => [],
            'revenue' => [],
            'orders' => []
        ];

        $dateRange = $this->getDateRange($period);
        $start = $dateRange['start']->copy();

        // تحديد عدد النقاط والفترة بناءً على النوع
        [$points, $interval] = match($period) {
            'this_week', 'last_week' => [7, 'day'],
            'this_month', 'last_month' => [$start->daysInMonth, 'day'],
            'this_year', 'last_year' => [12, 'month'],
            default => [7, 'day']
        };

        // استعلام تجميعي واحد لتحسين الأداء وتجنب الاستعلامات المتكررة داخل الحلقة
        if ($interval === 'day') {
            $dbData = Order::select(
                    DB::raw('DATE(created_at) as date_key'),
                    DB::raw("SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) as revenue"),
                    DB::raw('COUNT(*) as total_orders')
                )
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get()
                ->keyBy('date_key');
        } else {
            $dbData = Order::select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as date_key"),
                    DB::raw("SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) as revenue"),
                    DB::raw('COUNT(*) as total_orders')
                )
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->get()
                ->keyBy('date_key');
        }

        for ($i = 0; $i < $points; $i++) {
            $periodStart = $start->copy();
            
            // تحديد مفتاح البحث في البيانات المجمعة
            $dateKey = $interval === 'day' 
                ? $periodStart->format('Y-m-d')
                : $periodStart->format('Y-m');

            // استخدام translatedFormat للحصول على أسماء الأيام والأشهر باللغة العربية
            if ($interval === 'day') {
                if ($period === 'this_week' || $period === 'last_week') {
                    $chartData['labels'][] = $periodStart->translatedFormat('D');
                } else {
                    $chartData['labels'][] = $periodStart->format('j');
                }
            } else {
                $chartData['labels'][] = $periodStart->translatedFormat('M');
            }

            // جلب البيانات من المجموعة المحملة مسبقاً
            $record = $dbData->get($dateKey);
            $revenue = $record ? $record->revenue : 0;
            $orders = $record ? $record->total_orders : 0;

            $chartData['revenue'][] = floatval($revenue);
            $chartData['orders'][] = intval($orders);

            if ($interval === 'day') {
                $start->addDay();
            } else {
                $start->addMonth();
            }
        }

        return $chartData;
    }

    /**
     * إرجاع بيانات فارغة في حالة الخطأ
     */
    private function getEmptyAnalytics(): array
    {
        return [
            'current' => [
                'revenue' => 0,
                'total_orders' => 0,
                'orders_count' => 0,
                'delivered_count' => 0,
                'average_order_value' => 0
            ],
            'previous' => [
                'revenue' => 0,
                'total_orders' => 0,
                'orders_count' => 0,
                'delivered_count' => 0,
                'average_order_value' => 0
            ],
            'changes' => [
                'revenue' => 0,
                'orders' => 0,
            ],
            'period' => 'this_week',
            'date_range' => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek()
            ],
            'chart_data' => [
                'labels' => [],
                'revenue' => [],
                'orders' => []
            ]
        ];
    }

    /**
     * الحصول على ملخص الإيرادات الإجمالية
     */
    public function getOverallSummary(): array
    {
        return [
            'total_revenue' => Order::where('status', 'delivered')->sum('total') ?? 0,
            'total_orders' => Order::count(),
            'total_delivered' => Order::where('status', 'delivered')->count(),
            'total_pending' => Order::where('status', 'ordered')->count(),
            'total_cancelled' => Order::where('status', 'canceled')->count(),
        ];
    }
}
