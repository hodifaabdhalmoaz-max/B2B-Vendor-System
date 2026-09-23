<div class="account-nav-wrapper bg-white rounded-4 shadow-sm p-4 mb-4">
    <div class="user-info-brief mb-4 text-center border-bottom pb-3">
        @if(Auth::user()->profile_photo)
        <img src="{{ asset('storage/profile_photos/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="rounded-circle mb-2 border p-1" style="width: 70px; height: 70px; object-fit: cover;">
        @else
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 text-white shadow-sm" style="width: 70px; height: 70px; font-size: 2rem; font-weight: 700; background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">
            {{ mb_substr(Auth::user()->name, 0, 1, 'UTF-8') }}
        </div>
        @endif
        <h6 class="fw-bold mb-0">{{ Auth::user()->name }}</h6>
        <small class="text-muted">{{ Auth::user()->email }}</small>
    </div>

    <ul class="account-nav list-unstyled mb-0">
        <li class="mb-2">
            <a href="{{route('user.index')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('user.index') ? 'active' : '' }}">
                <i class="bi bi-grid-fill me-3 fs-5"></i>
                <span>لوحة التحكم</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('user.orders')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('user.orders') || Route::is('user.order.details') ? 'active' : '' }}">
                <i class="bi bi-bag-check-fill me-3 fs-5"></i>
                <span>طلباتي</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('user.profile')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('user.profile') ? 'active' : '' }}">
                <i class="bi bi-person-fill me-3 fs-5"></i>
                <span>تفاصيل الحساب</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('user.addresses')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('user.addresses') || Route::is('user.address.add') || Route::is('user.address.edit') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill me-3 fs-5"></i>
                <span>العناوين</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('user.wishlist')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('user.wishlist') ? 'active' : '' }}">
                <i class="bi bi-heart-fill me-3 fs-5"></i>
                <span>المفضلة</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('contact')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('contact') ? 'active' : '' }}">
                <i class="bi bi-headset me-3 fs-5"></i>
                <span>تواصل معنا</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('returns')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('returns') ? 'active' : '' }}">
                <i class="bi bi-arrow-counterclockwise me-3 fs-5"></i>
                <span>شروط الاستبدال والاسترجاع</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('privacy')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('privacy') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill me-3 fs-5"></i>
                <span>سياسة الخصوصية</span>
            </a>
        </li>
        <li class="mb-2">
            <a href="{{route('terms')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 transition {{ Route::is('terms') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill me-3 fs-5"></i>
                <span>الشروط والأحكام</span>
            </a>
        </li>
        <li class="mt-4 border-top pt-3">
            <form method="POST" action="{{route('logout')}}" id="logout-form">
                @csrf
                <a href="{{route('logout')}}" class="nav-item-link d-flex align-items-center p-3 rounded-3 text-danger transition" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-3 fs-5"></i>
                    <span>تسجيل الخروج</span>
                </a>
            </form>
        </li>
    </ul>
</div>

<style>
    .account-nav-wrapper {
        position: sticky;
        top: 100px;
    }

    .nav-item-link {
        color: #4b5563;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-item-link:hover {
        background-color: #f3f4f6;
        color: var(--Main, #007bff);
        transform: translateX(-5px);
    }

    .nav-item-link.active {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(212, 168, 83, 0.3);
    }

    .nav-item-link.text-danger:hover {
        background-color: #fee2e2;
        color: #dc2626 !important;
    }

    .transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    [dir="rtl"] .nav-item-link:hover {
        transform: translateX(5px);
    }
</style>
