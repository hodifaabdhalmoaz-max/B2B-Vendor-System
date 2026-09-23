@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>{{ __('admin.dashboard') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                {{ __('admin.total_orders') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOrders }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                {{ __('admin.total_revenue') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalRevenue }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                {{ __('admin.total_products') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                {{ __('admin.total_customers') }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCustomers }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin.recent_orders') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('admin.order_id') }}</th>
                                                    <th>{{ __('admin.customer') }}</th>
                                                    <th>{{ __('admin.total') }}</th>
                                                    <th>{{ __('admin.status') }}</th>
                                                    <th>{{ __('admin.date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentOrders as $order)
                                                <tr>
                                                    <td><a href="{{ route('admin.orders.show', $order->id) }}">#{{ $order->id }}</a></td>
                                                    <td>{{ $order->user->name }}</td>
                                                    <td>{{ $order->total }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $order->status_color }}">{{ $order->status }}</span>
                                                    </td>
                                                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin.top_selling_products') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('admin.product') }}</th>
                                                    <th>{{ __('admin.price') }}</th>
                                                    <th>{{ __('admin.sold') }}</th>
                                                    <th>{{ __('admin.revenue') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topSellingProducts as $product)
                                                <tr>
                                                    <td><a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name }}</a></td>
                                                    <td>{{ $product->regular_price }}</td>
                                                    <td>{{ $product->sold_count }}</td>
                                                    <td>{{ $product->revenue }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin.monthly_sales') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="monthlySalesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin.order_status_distribution') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie">
                                        <canvas id="orderStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin.admin_notes') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h5>{{ __('admin.contact_information') }}</h5>
                                        <p><strong>{{ __('admin.admin_name') }}:</strong> حذيفة عبدالمعز الحذيفي</p>
                                        <p><strong>{{ __('admin.admin_email') }}:</strong> <a href="mailto:hodifaabdhalmoaz@gmail.com">hodifaabdhalmoaz@gmail.com</a></p>
                                        <p><strong>{{ __('admin.admin_phone') }}:</strong> <a href="tel:+967777548421">777548421 967+</a> / <a href="tel:+967718706242">718706242 967+</a></p>
                                        <p><strong>{{ __('admin.admin_whatsapp') }}:</strong> <a href="https://wa.me/967718706242" target="_blank">718706242 967+</a></p>
                                    </div>

                                    <div>
                                        <h5>{{ __('admin.social_media') }}</h5>
                                        <p>
                                            <a href="https://www.facebook.com/share/1E3T83a8KD/" target="_blank" class="btn btn-sm btn-outline-primary me-1"><i class="fab fa-facebook-f"></i> Facebook</a>
                                            <a href="https://x.com/moaz_abdh" target="_blank" class="btn btn-sm btn-outline-dark me-1"><i class="fab fa-twitter"></i> X (Twitter)</a>
                                            <a href="https://www.instagram.com/invites/contact/?utm_source=ig_contact_invite&utm_medium=copy_link&utm_content=mwfgwqx" target="_blank" class="btn btn-sm btn-outline-danger me-1"><i class="fab fa-instagram"></i> Instagram</a>
                                            <a href="https://www.linkedin.com/in/hodifa-al-hodify-30644b289" target="_blank" class="btn btn-sm btn-outline-primary me-1"><i class="fab fa-linkedin"></i> LinkedIn</a>
                                            <a href="https://github.com/HA1234098765" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fab fa-github"></i> GitHub</a>
                                        </p>
                                    </div>
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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Sales Chart
    var ctx = document.getElementById('monthlySalesChart').getContext('2d');
    var monthlySalesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {
                !!json_encode($monthlySalesLabels) !!
            },
            datasets: [{
                label: '{{ __("admin.monthly_sales") }}',
                data: {
                    !!json_encode($monthlySalesData) !!
                },
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Order Status Chart
    var ctx2 = document.getElementById('orderStatusChart').getContext('2d');
    var orderStatusChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: {
                !!json_encode($orderStatusLabels) !!
            },
            datasets: [{
                data: {
                    !!json_encode($orderStatusData) !!
                },
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b'],
                hoverBorderColor: 'rgba(234, 236, 244, 1)',
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '70%'
        }
    });
</script>
@endsection