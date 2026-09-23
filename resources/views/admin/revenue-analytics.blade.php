@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="tf-section-2 mb-30">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap-mobile mb-30">
                <div class="flex items-center gap14">
                    <div class="image ic-bg">
                        <i class="icon-bar-chart"></i>
                    </div>
                    <div>
                        <h4>تحليل الإيرادات</h4>
                        <div class="body-text">تحليل شامل لإيرادات المتجر</div>
                    </div>
                </div>

                <!-- Period Selector -->
                <div class="flex items-center gap10">
                    <div class="dropdown default">
                        <button class="btn btn-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-calendar me-2"></i>
                            <span id="current-period">{{ $period == 'this_week' ? 'هذا الأسبوع' : ($period == 'last_week' ? 'الأسبوع الماضي' : ($period == 'this_month' ? 'هذا الشهر' : ($period == 'last_month' ? 'الشهر الماضي' : ($period == 'this_year' ? 'هذا العام' : 'العام الماضي')))) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item period-selector" href="#" data-period="this_week">هذا الأسبوع</a></li>
                            <li><a class="dropdown-item period-selector" href="#" data-period="last_week">الأسبوع الماضي</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item period-selector" href="#" data-period="this_month">هذا الشهر</a></li>
                            <li><a class="dropdown-item period-selector" href="#" data-period="last_month">الشهر الماضي</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item period-selector" href="#" data-period="this_year">هذا العام</a></li>
                            <li><a class="dropdown-item period-selector" href="#" data-period="last_year">العام الماضي</a></li>
                        </ul>
                    </div>

                    <button class="btn btn-primary" id="download-pdf">
                        <i class="icon-download me-2"></i>تحميل التقرير
                    </button>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loading-indicator" class="text-center py-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="flex gap20 flex-wrap-mobile" id="stats-container">
                <!-- Revenue Card -->
                <div class="w-quarter">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">إجمالي الإيرادات</div>
                                    <h4 id="total-revenue">{{ format_price($analytics['current']['revenue']) }}</h4>
                                </div>
                            </div>
                            <div class="box-icon-trending {{ $analytics['changes']['revenue'] >= 0 ? 'up' : 'down' }}" id="revenue-trend">
                                <i class="icon-trending-{{ $analytics['changes']['revenue'] >= 0 ? 'up' : 'down' }}"></i>
                                <div class="body-title number">{{ abs($analytics['changes']['revenue']) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Count Card -->
                <div class="w-quarter">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">عدد الطلبات</div>
                                    <h4 id="orders-count">{{ $analytics['current']['orders_count'] }}</h4>
                                </div>
                            </div>
                            <div class="box-icon-trending {{ $analytics['changes']['orders'] >= 0 ? 'up' : 'down' }}" id="orders-trend">
                                <i class="icon-trending-{{ $analytics['changes']['orders'] >= 0 ? 'up' : 'down' }}"></i>
                                <div class="body-title number">{{ abs($analytics['changes']['orders']) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Order Value Card -->
                <div class="w-quarter">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-trending-up"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">متوسط قيمة الطلب</div>
                                    <h4 id="avg-order-value">{{ format_price($analytics['current']['average_order_value']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivered Orders Card -->
                <div class="w-quarter">
                    <div class="wg-chart-default">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-check-circle"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">الطلبات المسلمة</div>
                                    <h4 id="delivered-count">{{ $analytics['current']['delivered_count'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="wg-box mt-30">
                <div class="flex items-center justify-between mb-20">
                    <h5>مخطط الإيرادات والطلب</h5>
                    <div class="flex gap10">
                        <div class="block-legend">
                            <div class="dot t1" style="background-color: #2377FC;"></div>
                            <div class="text-tiny">الإيرادات المحققة (ريال جديد)</div>
                        </div>
                        <div class="block-legend">
                            <div class="dot t2" style="background-color: #FFA500;"></div>
                            <div class="text-tiny">عدد الطلبات (طلب)</div>
                        </div>
                    </div>
                </div>
                <div style="height: 400px; position: relative;">
                    <canvas id="revenue-chart"></canvas>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="wg-box mt-30">
                <h5 class="mb-20">الملخص الإجمالي</h5>
                <div class="flex gap20 flex-wrap-mobile">
                    <div class="w-half">
                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">إجمالي الإيرادات (كل الأوقات)</div>
                                    <h4>{{ format_price($overallSummary['total_revenue']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-half">
                        <div class="wg-chart-default mb-20">
                            <div class="flex items-center gap14">
                                <div class="image ic-bg">
                                    <i class="icon-shopping-bag"></i>
                                </div>
                                <div>
                                    <div class="body-text mb-2">إجمالي الطلبات (كل الأوقات)</div>
                                    <h4>{{ $overallSummary['total_orders'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPeriod = '{{ $period }}';
    let chart = null;

    // Initialize chart
    initChart(@json($analytics['chart_data']));

    // Period selector
    document.querySelectorAll('.period-selector').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.dataset.period;
            loadData(period);
        });
    });

    // PDF download
    document.getElementById('download-pdf').addEventListener('click', function() {
        window.open(`{{ route('admin.revenue.pdf') }}?period=${currentPeriod}`, '_blank');
    });

    function loadData(period) {
        showLoading();
        currentPeriod = period;

        fetch(`{{ route('admin.revenue.data') }}?period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateUI(data.data);
                    updateChart(data.data.chart_data);
                } else {
                    showError('حدث خطأ في جلب البيانات');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('حدث خطأ في الاتصال');
            })
            .finally(() => {
                hideLoading();
            });
    }

    function updateUI(analytics) {
        // Update period label
        document.getElementById('current-period').textContent = getPeriodLabel(currentPeriod);

        // Update statistics
        document.getElementById('total-revenue').textContent = formatNumber(analytics.current.revenue) + ' ريال جديد';
        document.getElementById('orders-count').textContent = analytics.current.orders_count;
        document.getElementById('avg-order-value').textContent = formatNumber(analytics.current.average_order_value) + ' ريال جديد';
        document.getElementById('delivered-count').textContent = analytics.current.delivered_count;

        // Update trends
        updateTrend('revenue-trend', analytics.changes.revenue);
        updateTrend('orders-trend', analytics.changes.orders);
    }

    function updateTrend(elementId, change) {
        const element = document.getElementById(elementId);
        const isPositive = change >= 0;

        element.className = `box-icon-trending ${isPositive ? 'up' : 'down'}`;
        element.innerHTML = `
            <i class="icon-trending-${isPositive ? 'up' : 'down'}"></i>
            <div class="body-title number">${Math.abs(change)}%</div>
        `;
    }

    function initChart(chartData) {
        const ctx = document.getElementById('revenue-chart').getContext('2d');

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'الإيرادات المحققة',
                    data: chartData.revenue,
                    borderColor: '#2377FC',
                    backgroundColor: 'rgba(35, 119, 252, 0.1)',
                    yAxisID: 'y',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'عدد الطلبات',
                    data: chartData.orders,
                    borderColor: '#FFA500',
                    backgroundColor: 'rgba(255, 165, 0, 0.05)',
                    yAxisID: 'y1',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.datasetIndex === 0) {
                                    label += context.parsed.y.toLocaleString('ar-YE') + ' ريال جديد';
                                } else {
                                    label += context.parsed.y + ' طلب';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'الإيرادات (ريال جديد)',
                            font: {
                                size: 11
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('ar-YE') + ' ريال';
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'عدد الطلبات',
                            font: {
                                size: 11
                            }
                        },
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function updateChart(chartData) {
        if (chart) {
            chart.data.labels = chartData.labels;
            chart.data.datasets[0].data = chartData.revenue;
            chart.data.datasets[1].data = chartData.orders;
            chart.update();
        }
    }

    function showLoading() {
        document.getElementById('loading-indicator').style.display = 'block';
        document.getElementById('stats-container').style.opacity = '0.5';
    }

    function hideLoading() {
        document.getElementById('loading-indicator').style.display = 'none';
        document.getElementById('stats-container').style.opacity = '1';
    }

    function showError(message) {
        alert(message);
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num);
    }

    function getPeriodLabel(period) {
        const labels = {
            'this_week': 'هذا الأسبوع',
            'last_week': 'الأسبوع الماضي',
            'this_month': 'هذا الشهر',
            'last_month': 'الشهر الماضي',
            'this_year': 'هذا العام',
            'last_year': 'العام الماضي'
        };
        return labels[period] || 'هذا الأسبوع';
    }
});
</script>
@endpush
