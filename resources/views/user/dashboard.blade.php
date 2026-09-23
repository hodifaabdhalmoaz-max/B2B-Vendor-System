@extends('layouts.app')

@section('content')
<main class="pt-90">

    @include('user.components.dashboard-header', ['title' => 'لوحة التحكم', 'description' => 'مرحباً بك في لوحة تحكم حسابك الخاص', 'isDashboard' => true])
    <section class="my-account container">
        <!-- Desktop Dashboard (Hidden on Mobile/Tablet) -->
        <div class="d-none d-md-block">

            <div class="row">
                <div class="col-md-4 col-lg-3">
                    @include('user.account-nav')
                </div>
                <div class="col-md-8 col-lg-9">
                    <div class="page-content my-account__dashboard">
                        <!-- Welcome Banner -->
                        <div class="welcome-banner p-4 rounded-4 mb-4 position-relative overflow-hidden shadow-sm bg-white border">
                            <div class="row align-items-center position-relative" style="z-index: 1;">
                                <div class="col-md-8">
                                    <h4 class="fw-bold text-dark">مرحباً بك مجدداً، {{ Auth::user()->name }}! 👋</h4>
                                    <p class="text-muted mb-0 small">يسعدنا رؤيتك اليوم. يمكنك تتبع طلباتك، تعديل بياناتك، أو تصفح منتجاتك المفضلة بكل سهولة.</p>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="{{ route('user.profile') }}" class="btn btn-dark rounded-pill px-4 py-2 shadow-sm btn-sm">
                                        <i class="bi bi-pencil me-1"></i> تعديل الحساب
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <div class="stat-card p-4 rounded-4 shadow-sm h-100 transition border bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="stat-icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                                            <i class="bi bi-box-seam fs-4"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-muted small">إجمالي الطلبات</h6>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <h2 class="fw-bold mb-0 me-2">{{ $totalOrders }}</h2>
                                        <span class="text-muted small">طلب</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card p-4 rounded-4 shadow-sm h-100 transition border bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="stat-icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                                            <i class="bi bi-clock-history fs-4"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-muted small">بانتظار المعالجة</h6>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <h2 class="fw-bold mb-0 me-2">{{ $pendingOrders }}</h2>
                                        <span class="text-muted small">طلب</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card p-4 rounded-4 shadow-sm h-100 transition border bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="stat-icon-box bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                                            <i class="bi bi-check-circle fs-4"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-muted small">طلبات مكتملة</h6>
                                    </div>
                                    <div class="d-flex align-items-baseline">
                                        <h2 class="fw-bold mb-0 me-2">{{ $completedOrders }}</h2>
                                        <span class="text-muted small">طلب</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders Table -->
                        <div class="recent-orders-card bg-white rounded-4 shadow-sm overflow-hidden border">
                            <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0">آخر الطلبات</h5>
                                <a href="{{ route('user.orders') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">عرض الكل</a>
                            </div>

                            @if($recentOrders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4 py-3">رقم الطلب</th>
                                            <th class="py-3">التاريخ</th>
                                            <th class="py-3 text-center">الحالة</th>
                                            <th class="py-3">الإجمالي</th>
                                            <th class="pe-4 py-3 text-end">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentOrders as $order)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-bold">#{{ $order->id }}</span>
                                            </td>
                                            <td class="text-muted small">{{ $order->created_at->format('d M, Y') }}</td>
                                            <td class="text-center">
                                                @php
                                                $statusClass = [
                                                'delivered' => 'bg-success bg-opacity-10 text-success',
                                                'canceled' => 'bg-danger bg-opacity-10 text-danger',
                                                'ordered' => 'bg-warning bg-opacity-10 text-warning-dark',
                                                'shipped' => 'bg-info bg-opacity-10 text-info',
                                                ][$order->status] ?? 'bg-secondary bg-opacity-10 text-secondary';

                                                $statusText = [
                                                'ordered' => 'قيد الانتظار',
                                                'delivered' => 'تم التسليم',
                                                'canceled' => 'ملغي',
                                                'shipped' => 'تم الشحن',
                                                ][$order->status] ?? $order->status;
                                                @endphp
                                                <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}" style="font-size: 11px;">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark small">{{ format_price($order->total) }}</td>
                                            <td class="pe-4 text-end">
                                                <a href="{{ route('user.order.details', $order->id) }}" class="btn btn-icon btn-light rounded-circle shadow-sm" title="عرض التفاصيل">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-cart-plus display-6 text-muted"></i>
                                </div>
                                <h6 class="text-muted">لم تقم بإجراء أي طلبات بعد</h6>
                                <a href="{{ route('shop.index') }}" class="btn btn-primary rounded-pill mt-3 px-4">ابدأ التسوق الآن</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Dashboard (Visible only on Mobile/Tablet) -->
        <div class="mobile-dashboard d-md-none">

            <div class="quick-links-container mb-4 px-2">
                <div class="quick-links-card bg-white rounded-4 shadow-sm d-flex p-3 border">
                    <a href="#" class="quick-link-item flex-fill text-center text-decoration-none border-start">
                        <i class="bi bi-wallet2 fs-4 text-primary d-block mb-1"></i>
                        <span class="small text-muted">المحفظة</span>
                    </a>
                    <a href="{{ route('user.orders') }}" class="quick-link-item flex-fill text-center text-decoration-none border-start">
                        <i class="bi bi-bag fs-4 text-primary d-block mb-1"></i>
                        <span class="small text-muted">طلباتي</span>
                    </a>
                    <a href="{{ route('user.wishlist') }}" class="quick-link-item flex-fill text-center text-decoration-none">
                        <i class="bi bi-heart fs-4 text-primary d-block mb-1"></i>
                        <span class="small text-muted">المفضلة</span>
                    </a>
                </div>
            </div>

            <div class="dashboard-sections px-2">
                <h6 class="fw-bold text-dark mb-3 ps-1 small">حسابي</h6>
                <div class="list-card bg-white rounded-4 shadow-sm border mb-4 overflow-hidden">
                    <a href="{{ route('user.profile') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">إعدادات الملف الشخصي</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('user.addresses') }}" class="list-item d-flex align-items-center p-3 text-decoration-none">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-geo-alt text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">عناوين التوصيل</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                </div>

                <h6 class="fw-bold text-dark mb-3 ps-1 small">الإعدادات</h6>
                <div class="list-card bg-white rounded-4 shadow-sm border mb-4 overflow-hidden">
                    <div class="list-item d-flex align-items-center p-3 border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-geo-alt text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">المدينة</span>
                        <div class="ms-auto d-flex align-items-center text-muted small">
                            <span class="me-2">عدن</span>
                            <i class="bi bi-chevron-left"></i>
                        </div>
                    </div>
                    <div class="list-item d-flex align-items-center p-3">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-bank text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">العملة</span>
                        <div class="ms-auto d-flex align-items-center text-muted small">
                            <span class="me-2">ريال جديد</span>
                            <i class="bi bi-chevron-left"></i>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3 ps-1 small">مساعد دنيا الأطفال </h6>
                <div class="list-card bg-white rounded-4 shadow-sm border mb-4 overflow-hidden">
                    <a href="{{ route('contact') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-headset text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">تواصل معنا</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('returns') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-arrow-counterclockwise text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">الاستبدال والاسترجاع</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('privacy') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-shield-check text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">سياسة الخصوصية</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('terms') }}" class="list-item d-flex align-items-center p-3 text-decoration-none">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                        </div>
                        <span class="text-dark fw-medium small">الشروط والاحكام</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                </div>

                <div class="logout-section mt-5 pb-5 px-2">
                    <form method="POST" action="{{route('logout')}}" id="logout-form-mobile">
                        @csrf
                        <button type="submit" class="btn-logout-mobile w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-right me-2"></i> تسجيل الخروج
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .stat-card {
        background-color: #fff;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
    }

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
        background: var(--Main, #f0c14b);
        border-radius: 2px;
    }

    /* Mobile Dashboard Styles */
    .mobile-dashboard {
        background-color: #f8fafc;
        min-height: 100vh;
        margin: -20px -15px;
        /* Counter container padding */
        padding: 20px 15px;
    }

    .user-avatar-lg {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--Main, #f0c14b) 0%, #e0ac1a 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 800;
        border: 4px solid #fff;
    }

    .quick-link-item {
        color: #475569;
        transition: all 0.3s ease;
        border-radius: 12px;
        padding: 8px 0;
    }

    .quick-link-item i {
        color: #f0c14b !important;
        transition: all 0.3s ease;
    }

    .quick-link-item:hover,
    .quick-link-item:active {
        background-color: rgba(240, 193, 75, 0.05);
        transform: translateY(-2px);
    }

    .quick-link-item:hover i {
        transform: scale(1.15);
        color: #d4a853 !important;
    }

    .list-card .list-item {
        transition: all 0.3s ease;
    }

    .list-card .list-item:hover,
    .list-card .list-item:active {
        background-color: #fafafa;
        transform: translateX(-5px);
    }

    .icon-box {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        background: rgba(240, 193, 75, 0.1) !important;
        color: #f0c14b !important;
        transition: all 0.3s ease;
    }

    .icon-box i {
        color: #f0c14b !important;
    }

    .list-card .list-item:hover .icon-box {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(240, 193, 75, 0.3);
    }

    .list-card .list-item:hover .icon-box i {
        color: #fff !important;
    }

    .bi-chevron-left {
        transition: all 0.3s ease;
    }

    .list-card .list-item:hover .bi-chevron-left {
        color: #f0c14b !important;
        transform: translateX(-3px);
    }

    .btn-logout-mobile {
        background-color: #fff;
        color: #dc2626;
        border: 1px solid #fee2e2;
        padding: 14px;
        border-radius: 12px;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.05);
    }

    .btn-logout-mobile:active {
        background-color: #fef2f2;
        transform: scale(0.98);
    }

    .breadcrumb-item+.breadcrumb-item::before {
        float: right;
        padding-left: 0.5rem;
    }
</style>
@endsection