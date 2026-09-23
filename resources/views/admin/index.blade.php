@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    @yield('content')

    <div class="main-content-wrap">
        <div class="tf-section-2 mb-30">
            <div class="flex gap20 flex-wrap-mobile">
                <div class="w-half">

                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">إجمالي الطلبات</div>
                                    <h4>{{ $totalOrders }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">إجمالي المبلغ</div>
                                    <h4>{{ format_price($totalAmount) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">الطلبات المعلقة</div>
                                    <h4>{{ $pendingOrders }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">مبلغ الطلبات المعلقة</div>
                                    <h4>{{ format_price($pendingAmount) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="w-half">

                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">الطلبات المسلمة</div>
                                    <h4>{{ $deliveredOrders }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">مبلغ الطلبات المسلمة</div>
                                    <h4>{{ format_price($deliveredAmount) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">الطلبات الملغاة</div>
                                    <h4>{{ $cancelledOrders }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">مبلغ الطلبات الملغاة</div>
                                    <h4>{{ format_price($cancelledAmount) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between">
                    <h5>إيرادات الأرباح</h5>
                    <div class="dropdown default">
                        <button class="btn btn-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <span class="icon-more"><i class="icon-more-horizontal"></i></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a href="{{ route('admin.index', ['period' => 'this_week']) }}"
                                   class="dropdown-item {{ $period == 'this_week' ? 'active fw-bold' : '' }}">
                                   <i class="icon-calendar me-2"></i>هذا الأسبوع
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.index', ['period' => 'last_week']) }}"
                                   class="dropdown-item {{ $period == 'last_week' ? 'active fw-bold' : '' }}">
                                   <i class="icon-calendar me-2"></i>الأسبوع الماضي
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="{{ route('admin.index', ['period' => 'this_month']) }}"
                                   class="dropdown-item {{ $period == 'this_month' ? 'active fw-bold' : '' }}">
                                   <i class="icon-calendar me-2"></i>هذا الشهر
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.index', ['period' => 'last_month']) }}"
                                   class="dropdown-item {{ $period == 'last_month' ? 'active fw-bold' : '' }}">
                                   <i class="icon-calendar me-2"></i>الشهر الماضي
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="{{ route('admin.index', ['period' => 'this_year']) }}"
                                   class="dropdown-item {{ $period == 'this_year' ? 'active fw-bold' : '' }}">
                                   <i class="icon-calendar me-2"></i>هذا العام
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.index', ['period' => 'last_year']) }}"
                                   class="dropdown-item {{ $period == 'last_year' ? 'active fw-bold' : '' }}">
                                   <i class="icon-calendar me-2"></i>العام الماضي
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="flex flex-wrap gap40">
                    <div>
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t1"></div>
                                <div class="text-tiny">الإيرادات</div>
                            </div>
                        </div>
                        <div class="flex items-center gap10">
                            <h4>{{ format_price($revenueData['current_revenue']) }}</h4>
                            <div class="box-icon-trending {{ $revenueData['revenue_change'] >= 0 ? 'up' : 'down' }}">
                                <i class="icon-trending-{{ $revenueData['revenue_change'] >= 0 ? 'up' : 'down' }}"></i>
                                <div class="body-title number">{{ abs($revenueData['revenue_change']) }}%</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <div class="block-legend">
                                <div class="dot t2"></div>
                                <div class="text-tiny">الطلبات</div>
                            </div>
                        </div>
                        <div class="flex items-center gap10">
                            <h4>{{ format_price($revenueData['current_orders']) }}</h4>
                            <div class="box-icon-trending {{ $revenueData['orders_change'] >= 0 ? 'up' : 'down' }}">
                                <i class="icon-trending-{{ $revenueData['orders_change'] >= 0 ? 'up' : 'down' }}"></i>
                                <div class="body-title number">{{ abs($revenueData['orders_change']) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="line-chart-8" data-custom="true"></div>
            </div>

        </div>
        <div class="tf-section mb-30">

            <div class="wg-box">
                <div class="flex items-center justify-between">
                    <h5>الطلبات الحديثة</h5>
                    <div class="dropdown default">
                        <a class="btn btn-secondary dropdown-toggle" href="{{ route('admin.orders') }}">
                            <span class="view-all">عرض الكل</span>
                        </a>
                    </div>
                </div>
                <div class="wg-table table-all-user">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                    <th class="text-center" style="width: 80px">رقم الطلب</th>
                                    <th class="text-center">الاسم</th>
                                    <th class="text-center">الهاتف</th>
                                    <th class="text-center">المجموع الفرعي</th>

                                    <th class="text-center">الإجمالي</th>
                                    <th class="text-center">الحالة</th>
                                    <th class="text-center">تاريخ الطلب</th>
                                    <th class="text-center">إجمالي العناصر</th>
                                    <th class="text-center">تاريخ التسليم</th>
                                    <th class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td class="text-center">{{ $order->id }}</td>
                                    <td class="text-center">{{ $order->name }}</td>
                                    <td class="text-center">{{ $order->phone }}</td>
                                    <td class="text-center">{{ format_price($order->subtotal) }}</td>

                                    <td class="text-center">{{ format_price($order->total) }}</td>
                                    <td class="text-center">
                                        <span class="badge
                                            @if($order->status == 'ordered') bg-warning text-dark
                                            @elseif($order->status == 'delivered') bg-success
                                            @elseif($order->status == 'canceled') bg-danger
                                            @else bg-info
                                            @endif">
                                            @if($order->status == 'ordered') مطلوب
                                            @elseif($order->status == 'delivered') مُسلم
                                            @elseif($order->status == 'canceled') ملغي
                                            @else {{ $order->status }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-center">{{ $order->orderItems->sum('quantity') }}</td>
                                    <td class="text-center">
                                        @if($order->delivered_date)
                                            {{ $order->delivered_date->format('Y-m-d') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="list-icon-function justify-content-center">
                                            <a href="{{ route('admin.order.details', $order->id) }}" title="التفاصيل">
                                                <div class="item eye">
                                                    <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                                                </div>
                                            </a>
                                            <a href="{{ route('admin.order.tracking', $order->id) }}" title="تتبع الطلب">
                                                <div class="item edit">
                                                    <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i>
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">لا توجد طلبات حتى الآن</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // تحديث بيانات الرسم البياني
    document.addEventListener('DOMContentLoaded', function() {
        // التأكد من وجود العنصر
        const chartElement = document.querySelector("#line-chart-8");
        if (!chartElement) return;

        // البيانات من الخادم
        var chartData = @json($chartData);

        // التحقق من صحة البيانات
        if (!chartData || !chartData.labels || !chartData.revenue) {
            console.error('بيانات الرسم البياني غير صحيحة');
            return;
        }

        // إعداد الرسم البياني
        var options = {
            series: [{
                name: 'الإيرادات',
                data: chartData.revenue || []
            }, {
                name: 'الطلبات',
                data: chartData.orders || []
            }, {
                name: 'الملغاة',
                data: chartData.canceled || []
            }],
            chart: {
                type: 'bar',
                height: 325,
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '10px',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                show: false,
            },
            colors: ['#2377FC', '#FFA500', '#FF0000'],
            stroke: {
                show: false,
            },
            xaxis: {
                labels: {
                    style: {
                        colors: '#212529',
                    },
                },
                categories: chartData.labels || [],
            },
            yaxis: {
                show: false,
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "$ " + (val || 0).toFixed(2)
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 250
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '15px'
                        }
                    }
                }
            }]
        };

        // إنشاء الرسم البياني
        try {
            var chart = new ApexCharts(chartElement, options);
            chart.render();
        } catch (error) {
            console.error('خطأ في إنشاء الرسم البياني:', error);
        }
    });
</script>
@endpush
