@extends('layouts.admin')
@section('content')
<div class="main-content-wrap">
    <div class="tf-section-2 mb-30">
        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <h5>إحصائيات الطلبات</h5>
                <div class="flex gap10">
                    <a href="{{ route('admin.orders') }}" class="tf-button style-1 w208">
                        <i class="icon-arrow-left"></i>
                        العودة للطلبات
                    </a>
                </div>
            </div>
            
            <!-- فلتر التاريخ -->
            <form method="GET" class="mb-30">
                <div class="row">
                    <div class="col-md-4">
                        <fieldset class="name">
                            <div class="body-title">من تاريخ</div>
                            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="mb-10">
                        </fieldset>
                    </div>
                    <div class="col-md-4">
                        <fieldset class="name">
                            <div class="body-title">إلى تاريخ</div>
                            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="mb-10">
                        </fieldset>
                    </div>
                    <div class="col-md-4">
                        <fieldset class="name">
                            <div class="body-title">&nbsp;</div>
                            <button type="submit" class="tf-button w208">تطبيق الفلتر</button>
                        </fieldset>
                    </div>
                </div>
            </form>

            <!-- الإحصائيات العامة -->
            <div class="row mb-30">
                <div class="col-md-3">
                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image">
                                    <div class="icon-shopping-cart"></div>
                                </div>
                                <div>
                                    <div class="body-text mb-2">إجمالي الطلبات</div>
                                    <h4>{{ $totalOrders }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="wg-chart-default mb-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap14">
                                <div class="image">
                                    <div class="icon-dollar-sign"></div>
                                </div>
                                <div>
                                    <div class="body-text mb-2">إجمالي المبيعات</div>
                                    <h4>{{ format_price($totalSales) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الطلبات حسب الحالة -->
            <div class="wg-box mb-30">
                <h6>الطلبات حسب الحالة</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>الحالة</th>
                                <th>العدد</th>
                                <th>النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordersByStatus as $status => $count)
                            <tr>
                                <td>
                                    @if($status == 'ordered')
                                        <span class="badge bg-warning">مطلوب</span>
                                    @elseif($status == 'processing')
                                        <span class="badge bg-info">قيد المعالجة</span>
                                    @elseif($status == 'shipped')
                                        <span class="badge bg-primary">تم الشحن</span>
                                    @elseif($status == 'delivered')
                                        <span class="badge bg-success">تم التسليم</span>
                                    @elseif($status == 'canceled')
                                        <span class="badge bg-danger">ملغي</span>
                                    @endif
                                </td>
                                <td>{{ $count }}</td>
                                <td>{{ $totalOrders > 0 ? round(($count / $totalOrders) * 100, 1) : 0 }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- المبيعات اليومية -->
            <div class="wg-box mb-30">
                <h6>المبيعات اليومية</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>عدد الطلبات</th>
                                <th>إجمالي المبيعات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesByDay as $sale)
                            <tr>
                                <td>{{ $sale->date }}</td>
                                <td>{{ $sale->order_count }}</td>
                                <td>{{ format_price($sale->total_sales) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">لا توجد مبيعات في هذه الفترة</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- أفضل العملاء -->
            <div class="wg-box">
                <h6>أفضل العملاء</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>عدد الطلبات</th>
                                <th>إجمالي الإنفاق</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $customer)
                            <tr>
                                <td>{{ $customer['user']->name ?? 'عميل محذوف' }}</td>
                                <td>{{ $customer['order_count'] }}</td>
                                <td>{{ format_price($customer['total_spent']) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">لا توجد بيانات عملاء</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
