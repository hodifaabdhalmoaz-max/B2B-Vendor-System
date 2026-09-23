<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RevenueAnalyticsController extends Controller
{
    protected RevenueAnalyticsService $revenueService;

    public function __construct(RevenueAnalyticsService $revenueService)
    {
        $this->middleware(['auth', 'admin']);
        $this->revenueService = $revenueService;
    }

    /**
     * عرض صفحة تحليل الإيرادات
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_week');

        // التحقق من صحة الفترة
        $allowedPeriods = ['this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'last_year'];
        if (!in_array($period, $allowedPeriods)) {
            $period = 'this_week';
        }

        // الحصول على تحليل الإيرادات
        $analytics = $this->revenueService->getRevenueAnalytics($period);

        // الحصول على الملخص الإجمالي
        $overallSummary = $this->revenueService->getOverallSummary();

        return view('admin.revenue-analytics', compact('analytics', 'overallSummary', 'period'));
    }

    /**
     * الحصول على البيانات عبر AJAX
     */
    public function getData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'this_week');

        // التحقق من صحة الفترة
        $allowedPeriods = ['this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'last_year'];
        if (!in_array($period, $allowedPeriods)) {
            return response()->json(['error' => 'فترة غير صحيحة'], 400);
        }

        try {
            $analytics = $this->revenueService->getRevenueAnalytics($period);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في جلب بيانات الإيرادات: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في جلب البيانات'
            ], 500);
        }
    }

    /**
     * تحميل تقرير PDF
     */
    public function downloadPDF(Request $request)
    {
        $period = $request->get('period', 'this_week');

        // التحقق من صحة الفترة
        $allowedPeriods = ['this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'last_year'];
        if (!in_array($period, $allowedPeriods)) {
            $period = 'this_week';
        }

        try {
            // الحصول على البيانات
            $analytics = $this->revenueService->getRevenueAnalytics($period);
            $overallSummary = $this->revenueService->getOverallSummary();

            // إعداد البيانات للتقرير
            $reportData = [
                'analytics' => $analytics,
                'overall_summary' => $overallSummary,
                'period' => $period,
                'period_label' => $this->getPeriodLabel($period),
                'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'date_range' => [
                    'start' => $analytics['date_range']['start']->format('Y-m-d'),
                    'end' => $analytics['date_range']['end']->format('Y-m-d')
                ]
            ];

            // إنشاء PDF
            $pdf = Pdf::loadView('admin.reports.revenue-pdf', $reportData);

            // تحديد اسم الملف
            $filename = 'revenue-report-' . $period . '-' . Carbon::now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ في إنشاء التقرير: ' . $e->getMessage());
        }
    }

    /**
     * الحصول على تسمية الفترة بالعربية
     */
    private function getPeriodLabel(string $period): string
    {
        return match($period) {
            'this_week' => 'هذا الأسبوع',
            'last_week' => 'الأسبوع الماضي',
            'this_month' => 'هذا الشهر',
            'last_month' => 'الشهر الماضي',
            'this_year' => 'هذا العام',
            'last_year' => 'العام الماضي',
            default => 'هذا الأسبوع'
        };
    }

    /**
     * إضافة دالة getDateRange المفقودة
     */
    private function getDateRange(string $period): array
    {
        $now = \Carbon\Carbon::now();

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
     * عرض الإحصائيات المتقدمة
     */
    public function advancedStats(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'this_month');

            // إحصائيات متقدمة
            $stats = [
                'top_selling_days' => $this->getTopSellingDays($period),
                'revenue_by_status' => $this->getRevenueByStatus($period),
                'growth_trend' => $this->getGrowthTrend($period)
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في جلب الإحصائيات المتقدمة: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في جلب الإحصائيات المتقدمة'
            ], 500);
        }
    }

    /**
     * الحصول على أفضل أيام المبيعات
     */
    private function getTopSellingDays(string $period): array
    {
        $dateRange = $this->getDateRange($period);

        return DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'delivered')
            ->groupBy('date')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * الحصول على الإيرادات حسب حالة الطلب
     */
    private function getRevenueByStatus(string $period): array
    {
        $dateRange = $this->getDateRange($period);

        return DB::table('orders')
            ->select('status', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    /**
     * الحصول على اتجاه النمو
     */
    private function getGrowthTrend(string $period): array
    {
        // حساب النمو للأشهر الثلاثة الماضية
        $months = [];
        for ($i = 2; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $revenue = DB::table('orders')
                ->where('status', 'delivered')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total');

            $months[] = [
                'month' => $month->format('M Y'),
                'revenue' => floatval($revenue)
            ];
        }

        return $months;
    }
}
