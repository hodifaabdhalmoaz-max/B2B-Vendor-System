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
                    <div class="page-content my-account__dashboard bg-white p-4 p-lg-5 rounded-4 shadow-sm border">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-emoji-smile text-primary fs-3"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1">مرحباً، {{Auth::user()->name}}!</h3>
                                <p class="text-muted mb-0">نحن سعداء برؤيتك مرة أخرى في متجرنا.</p>
                            </div>
                        </div>
                        <p class="fs-5 leading-relaxed text-secondary">من لوحة تحكم حسابك يمكنك بسهولة متابعة <a class="text-primary fw-bold text-decoration-none border-bottom border-primary border-2 pb-1" href="{{route('user.orders')}}">طلباتك الأخيرة</a>، وإدارة <a class="text-primary fw-bold text-decoration-none border-bottom border-primary border-2 pb-1" href="{{route('user.addresses')}}">عناوين الشحن</a>، وتعديل <a class="text-primary fw-bold text-decoration-none border-bottom border-primary border-2 pb-1" href="{{route('user.profile')}}">كلمة المرور وتفاصيل حسابك الشخصي.</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Dashboard (Visible only on Mobile/Tablet) -->
        <div class="mobile-dashboard d-md-none">

            <div class="quick-links-container mb-4 px-2">
                <div class="quick-links-card bg-white rounded-4 shadow-sm d-flex p-3 border">
                    <a href="#" class="quick-link-item flex-fill text-center text-decoration-none border-start">
                        <i class="bi bi-wallet2 fs-4 text-primary d-block mx-auto mb-1"></i>
                        <span class="small text-muted">المحفظة</span>
                    </a>
                    <a href="{{ route('user.orders') }}" class="quick-link-item flex-fill text-center text-decoration-none border-start">
                        <i class="bi bi-bag fs-4 text-primary d-block mx-auto mb-1"></i>
                        <span class="small text-muted">طلباتي</span>
                    </a>
                    <a href="{{ route('user.wishlist') }}" class="quick-link-item flex-fill text-center text-decoration-none">
                        <i class="bi bi-heart fs-4 text-primary d-block mx-auto mb-1"></i>
                        <span class="small text-muted">المفضلة</span>
                    </a>
                </div>
            </div>

            <div class="dashboard-sections px-2">
                <h6 class="fw-bold text-dark mb-3 ps-1">حسابي</h6>
                <div class="list-card bg-white rounded-4 shadow-sm border mb-4 overflow-hidden">
                    <a href="{{ route('user.profile') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-person text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">إعدادات الملف الشخصي</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('user.addresses') }}" class="list-item d-flex align-items-center p-3 text-decoration-none">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-geo-alt text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">عناوين التوصيل</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                </div>

                <h6 class="fw-bold text-dark mb-3 ps-1">الإعدادات</h6>
                <div class="list-card bg-white rounded-4 shadow-sm border mb-4 overflow-hidden">
                    <div class="list-item d-flex align-items-center p-3 border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-geo-alt text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">المدينة</span>
                        <div class="ms-auto d-flex align-items-center text-muted small">
                            <span class="me-2">عدن</span>
                            <i class="bi bi-chevron-left"></i>
                        </div>
                    </div>
                    <div class="list-item d-flex align-items-center p-3">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-bank text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">العملة</span>
                        <div class="ms-auto d-flex align-items-center text-muted small">
                            <span class="me-2">ريال جديد</span>
                            <i class="bi bi-chevron-left"></i>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3 ps-1">مساعد دنيا الأطفال</h6>
                <div class="list-card bg-white rounded-4 shadow-sm border mb-4 overflow-hidden">
                    <a href="{{ route('contact') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-headset text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">تواصل معنا</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('returns') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-arrow-counterclockwise text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">الاستبدال والاسترجاع</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('privacy') }}" class="list-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-shield-check text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">سياسة الخصوصية</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                    <a href="{{ route('terms') }}" class="list-item d-flex align-items-center p-3 text-decoration-none">
                        <div class="icon-box bg-light rounded-circle me-3">
                            <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                        </div>
                        <span class="text-dark fw-medium">الشروط والاحكام</span>
                        <i class="bi bi-chevron-left ms-auto text-muted small"></i>
                    </a>
                </div>

                <div class="logout-section mt-5 pb-5">
                    <form method="POST" action="{{route('logout')}}" id="logout-form-mobile">
                        @csrf
                        <button type="submit" class="btn-logout-mobile w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-right me-2 fs-5"></i> تسجيل الخروج
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    /* Desktop Styles */
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
        width: 40px;
        height: 4px;
        background: var(--Main);
        border-radius: 2px;
    }

    .rounded-4 {
        border-radius: 1.2rem !important;
    }

    /* Mobile Styles */
    .mobile-dashboard {
        background: #fdfdfd;
        min-height: 100vh;
        padding-top: 10px;
    }

    .user-avatar-lg {
        width: 90px;
        height: 90px;
        background: linear-gradient(135deg, #007bff, #00d2ff);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
        border: 4px solid #fff;
    }

    .quick-links-card .quick-link-item:last-child {
        border-start: 0;
    }

    .icon-box {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .list-item:active {
        background-color: #f8f9fa;
    }

    .btn-logout-mobile {
        background-color: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
        padding: 14px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.2s ease;
    }

    .btn-logout-mobile:active {
        transform: scale(0.98);
        background-color: #ffe4e6;
    }

    [dir="rtl"] .ms-auto {
        margin-right: auto !important;
        margin-left: 0 !important;
    }

    [dir="rtl"] .me-3 {
        margin-left: 1rem !important;
        margin-right: 0 !important;
    }

    [dir="rtl"] .border-start {
        border-right: 1px solid #dee2e6 !important;
        border-left: 0 !important;
    }

    [dir="rtl"] .border-start:first-child {
        border-right: 0 !important;
    }
</style>
@endsection
