@extends('layouts.app')

@section('content')
<main class="pt-90">
    
    @include('user.components.dashboard-header', ['title' => 'تفاصيل الطلب', 'description' => 'عرض كافة التفاصيل الخاصة بطلبك رقم #' . $order->id])
    <section class="my-account container">
        <div class="row">
            <div class="col-lg-3 d-none d-md-block">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__order-details">
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h4 class="fw-bold mb-3">الطلب #{{ $order->id }}</h4>
                                    <p class="text-muted mb-1">تاريخ الطلب: <span class="text-dark fw-medium">{{ $order->created_at->format('Y-m-d H:i') }}</span></p>
                                    <p class="text-muted mb-0">حالة الطلب: 
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
                                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="{{ $statusStyle }}">
                                            {{ $statusText }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <h5 class="fw-bold mb-2">بيانات الدفع</h5>
                                    <p class="text-muted mb-1">طريقة الدفع: <span class="text-dark fw-medium">{{ $order->transaction->paymentMethod->name ?? $order->transaction->mode }}</span></p>
                                    <p class="text-muted mb-0">تاريخ الدفع: <span class="text-dark fw-medium">{{ $order->transaction->created_at->format('Y-m-d') }}</span></p>
                                </div>
                            </div>

                            <hr class="my-4 opacity-10">

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5 class="fw-bold mb-3">عنوان التوصيل</h5>
                                    <div class="bg-light p-3 rounded-3">
                                        <p class="mb-1 fw-bold">{{ $order->name }}</p>
                                        <p class="mb-1 text-muted"><i class="bi bi-geo-alt ms-1"></i> {{ $order->city }}, {{ $order->address }}</p>
                                        <p class="mb-0 text-muted"><i class="bi bi-telephone ms-1"></i> {{ $order->phone }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-4 mt-md-0">
                                    <h5 class="fw-bold mb-3">ملخص الطلب</h5>
                                    <div class="bg-light p-3 rounded-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>المجموع الفرعي:</span>
                                            <span class="fw-medium">{{ format_price($order->subtotal) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>رسوم التوصيل:</span>
                                            <span class="fw-medium">{{ format_price($order->shipping_fee ?? 0) }}</span>
                                        </div>
                                        @if($order->discount > 0)
                                        <div class="d-flex justify-content-between mb-2 text-danger">
                                            <span>الخصم:</span>
                                            <span>-{{ format_price($order->discount) }}</span>
                                        </div>
                                        @endif
                                        <div class="d-flex justify-content-between pt-2 border-top mt-2">
                                            <span class="fw-bold h5 mb-0">الإجمالي:</span>
                                            <span class="fw-bold h5 mb-0 text-primary">{{ format_price($order->total) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-4 mt-5">منتجات الطلب</h5>
                            <div class="order-items-list">
                                @foreach($order->orderItems as $item)
                                <div class="item-card bg-white border rounded-4 mb-3 overflow-hidden transition shadow-hover">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-4 col-md-2">
                                            <div class="item-img-wrapper bg-light p-2 h-100 d-flex align-items-center justify-content-center" style="min-height: 120px;">
                                                @if($item->product->image)
                                                    <img src="{{ asset('storage/products/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="img-fluid rounded-3 shadow-sm">
                                                @else
                                                    <img src="{{ asset('assets/images/placeholder.png') }}" alt="{{ $item->product->name }}" class="img-fluid rounded-3 shadow-sm">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-8 col-md-10">
                                            <div class="card-body p-3 p-md-4">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6 mb-2 mb-md-0">
                                                        <h6 class="fw-bold mb-1 text-dark fs-5">{{ $item->product->name }}</h6>
                                                        <x-order-item-options :options="$item->options" compact />
                                                        <p class="text-muted small mb-0 d-none d-md-block">{{ Str::limit($item->product->short_description, 100) }}</p>
                                                        <p class="text-muted small mb-0 d-block d-md-none">{{ Str::limit($item->product->short_description, 40) }}</p>
                                                    </div>
                                                    <div class="col-md-3 text-md-center mb-2 mb-md-0">
                                                        <div class="qty-info d-flex align-items-center justify-content-md-center">
                                                            <span class="text-muted small me-2">الكمية:</span>
                                                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $item->quantity }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 text-md-end">
                                                        <div class="price-info">
                                                            <span class="text-muted small d-md-none me-2">السعر:</span>
                                                            <span class="h5 fw-bold text-primary mb-0">{{ format_price($item->price) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('user.orders') }}" class="btn btn-dark px-4 py-2 rounded-pill">
                            العودة للطلبات
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .page-title {
        font-weight: 800;
        margin-bottom: 2rem;
        position: relative;
        display: inline-block;
    }
    .page-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        right: 0;
        width: 50px;
        height: 4px;
        background: var(--Main);
        border-radius: 2px;
    }
    
    .item-card {
        transition: all 0.3s ease;
    }
    
    .shadow-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
        border-color: var(--Main) !important;
    }
    
    .item-img-wrapper img {
        max-height: 100px;
        object-fit: contain;
    }
    
    .qty-info .badge {
        font-size: 0.9rem;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .item-img-wrapper {
            min-height: 100px !important;
        }
        .item-card .card-body {
            padding: 1rem !important;
        }
    }

    @media print {
        .col-lg-3, .btn, footer, header {
            display: none !important;
        }
        .col-lg-9 {
            width: 100% !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        .item-card {
            break-inside: avoid;
        }
    }
</style>
@endsection
