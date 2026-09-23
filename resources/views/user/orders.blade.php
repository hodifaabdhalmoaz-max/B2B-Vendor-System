@extends('layouts.app')

@section('content')
<main class="pt-90">
    
    @include('user.components.dashboard-header', ['title' => 'طلباتي', 'description' => 'تتبع وإدارة جميع طلباتك السابقة والحالية بسهولة'])
    <section class="my-account container">

        <div class="row">
            <div class="col-lg-3 d-none d-md-block">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__orders">
                    @if($orders->count() > 0)
                        <div class="row g-4">
                            @foreach($orders as $order)
                                <div class="col-md-6">
                                    <div class="card order-card h-100 shadow-sm border-0 rounded-4 bg-white transition">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle p-2 me-3 d-flex align-items-center justify-content-center icon-golden-box" style="width: 45px; height: 45px;">
                                                        <i class="bi bi-box-seam fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0">طلب #{{ $order->id }}</h6>
                                                        <small class="text-muted">{{ $order->created_at->format('d M, Y') }}</small>
                                                    </div>
                                                </div>
                                                @php
                                                    $statusStyle = [
                                                        'delivered' => 'background-color: #d1e7dd; color: #0f5132;',
                                                        'canceled' => 'background-color: #f8d7da; color: #842029;',
                                                        'ordered' => 'background-color: #fff3cd; color: #664d03;',
                                                        'shipped' => 'background-color: #cff4fc; color: #055160;',
                                                    ][$order->status] ?? 'background-color: #e2e3e5; color: #41464a;';
                                                    
                                                    $statusText = [
                                                        'ordered' => 'قيد الانتظار',
                                                        'delivered' => 'مسلم',
                                                        'canceled' => 'ملغي',
                                                        'shipped' => 'تم الشحن',
                                                    ][$order->status] ?? $order->status;
                                                @endphp
                                                <span class="badge rounded-pill px-3 py-2 fw-bold" style="{{ $statusStyle }}">
                                                    {{ $statusText }}
                                                </span>
                                            </div>
                                            
                                            <div class="order-summary-box bg-light rounded-3 p-3 mb-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">المبلغ الإجمالي</span>
                                                    <span class="fw-bold text-dark">{{ format_price($order->total) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-0">
                                                    <span class="text-muted small">طريقة الدفع</span>
                                                    <span class="text-dark small fw-medium">{{ $order->transaction->paymentMethod->name ?? ($order->transaction->mode == 'cod' ? 'دفع عند الاستلام' : $order->transaction->mode) }}</span>
                                                </div>
                                            </div>

                                            <div class="d-grid gap-2">
                                                @if($order->status == 'canceled')
                                                    <a href="#" class="btn btn-golden rounded-pill py-2 shadow-sm fw-bold">إعادة الطلب</a>
                                                @endif

                                                <a href="{{ route('user.order.details', $order->id) }}" class="btn btn-outline-golden rounded-pill py-2 fw-bold">
                                                    <i class="bi bi-eye me-1"></i> عرض التفاصيل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-5 d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border-dashed">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                                <i class="bi bi-bag display-4 text-muted"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">ليس لديك أي طلبات بعد</h4>
                            <p class="text-muted mb-4 px-3">ابدأ رحلة التسوق اليوم واكتشف أفضل المنتجات لأطفالك!</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-golden rounded-pill px-5 py-3 shadow-sm">ابدأ التسوق الآن</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .order-card {
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9 !important;
    }
    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
        border-color: #f0c14b !important;
    }
    .icon-golden-box {
        background: rgba(240, 193, 75, 0.1);
        color: #f0c14b;
        transition: all 0.3s ease;
    }
    .order-card:hover .icon-golden-box {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(240, 193, 75, 0.3);
    }
    .btn-golden {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%) !important;
        border: none !important;
        color: white !important;
        transition: all 0.3s ease;
    }
    .btn-golden:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(240, 193, 75, 0.3) !important;
    }
    .btn-outline-golden {
        color: #d4a853;
        border: 1px solid #d4a853;
        background: transparent;
        transition: all 0.3s ease;
    }
    .btn-outline-golden:hover {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 5px 15px rgba(240, 193, 75, 0.3);
    }
    .order-summary-box {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .order-card:hover .order-summary-box {
        background-color: rgba(240, 193, 75, 0.03) !important;
        border-color: rgba(240, 193, 75, 0.2);
    }
    .text-warning-dark { color: #854d0e; }
    .rounded-4 { border-radius: 1rem !important; }
    
    .page-title {
        font-weight: 800;
        position: relative;
        padding-bottom: 10px;
    }
    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 40px;
        height: 4px;
        background: #f0c14b;
        border-radius: 2px;
    }
    
    .border-dashed {
        border: 2px dashed #e2e8f0 !important;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        float: right;
        padding-left: 0.5rem;
    }
    
    .transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
@endsection
