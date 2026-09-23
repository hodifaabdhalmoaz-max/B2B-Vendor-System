@php
    $user = auth()->user();
    $fallbackUrl = isset($isDashboard) && $isDashboard ? route('home.index') : route('user.index');
    $previousUrl = url()->previous();
    $backUrl = $previousUrl && $previousUrl !== url()->current() ? $previousUrl : $fallbackUrl;
    $initial = $user?->name ? mb_substr($user->name, 0, 1, 'UTF-8') : 'U';
@endphp

@push('styles')
<style>
    .customer-page-header {
        position: relative;
        z-index: 5;
        overflow: hidden;
        min-height: 214px;
        background:
            radial-gradient(circle at 18% 22%, rgba(255, 255, 255, 0.28) 0 5px, transparent 6px),
            radial-gradient(circle at 76% 18%, rgba(255, 255, 255, 0.18) 0 4px, transparent 5px),
            linear-gradient(165deg, #64c9cf 0%, #6fd7cf 42%, #ffb48d 100%);
        border-bottom-right-radius: 38px;
        border-bottom-left-radius: 38px;
        box-shadow: 0 12px 28px rgba(100, 201, 207, 0.24);
    }

    .customer-page-header__bar {
        position: relative;
        display: grid;
        grid-template-columns: 44px 1fr 44px;
        align-items: end;
        max-width: 480px;
        margin: 0 auto;
        min-height: 176px;
        padding: 34px 22px 48px;
    }

    .customer-page-header__back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border: 0;
        background: transparent;
        color: #fff;
        text-decoration: none;
        font-size: 34px;
        line-height: 1;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    .customer-page-header__title {
        margin: 0;
        max-width: 100%;
        overflow-wrap: anywhere;
        color: #fff;
        text-align: center;
        text-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
        white-space: normal;
        font-size: clamp(36px, 12vw, 56px);
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1.2;
    }

    .customer-page-profile {
        max-width: 480px;
        margin: -24px auto 0;
        padding: 34px 14px 20px;
        background: #fff;
        border-top-right-radius: 28px;
        border-top-left-radius: 28px;
        text-align: center;
    }

    .customer-page-profile__avatar {
        position: relative;
        display: inline-flex;
        width: 96px;
        height: 96px;
        align-items: center;
        justify-content: center;
        border: 3px solid #f3d373;
        border-radius: 50%;
        background: #fff8e6;
        color: #c79b33;
        font-size: 34px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
    }

    .customer-page-profile__avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .customer-page-profile__edit {
        position: absolute;
        right: -2px;
        bottom: 2px;
        display: inline-flex;
        width: 30px;
        height: 30px;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 50%;
        background: #111;
        color: #fff;
        font-size: 14px;
    }

    .customer-page-profile__name {
        margin: 10px 0 0;
        color: #111;
        font-size: 18px;
        font-weight: 800;
    }

    @media (max-width: 767.98px) {
        body:has(.customer-page-header) main.pt-90 {
            padding-top: 0 !important;
        }

        body:has(.customer-page-header) .my-account.container {
            max-width: 480px;
            padding-inline: 10px;
        }
    }

    @media (min-width: 768px) {
        .customer-page-header {
            min-height: 230px;
            margin-top: 18px;
            border-bottom-right-radius: 48px;
            border-bottom-left-radius: 48px;
        }

        .customer-page-header__bar,
        .customer-page-profile {
            max-width: 1140px;
        }

        .customer-page-header__bar {
            min-height: 190px;
        }
    }
</style>
@endpush

<section class="customer-page-header" dir="rtl">
    <div class="customer-page-header__bar">
        <a class="customer-page-header__back" href="{{ $backUrl }}" aria-label="رجوع">
            <i class="bi bi-arrow-right"></i>
        </a>

        <h1 class="customer-page-header__title">{{ $title }}</h1>

        <span aria-hidden="true"></span>
    </div>

    @if(isset($isDashboard) && $isDashboard && $user)
        <div class="customer-page-profile">
            <a class="customer-page-profile__avatar" href="{{ route('user.profile') }}" aria-label="تعديل صورة الحساب">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/profile_photos/'.$user->profile_photo) }}" alt="{{ $user->name }}">
                @else
                    <span>{{ $initial }}</span>
                @endif
                <span class="customer-page-profile__edit" aria-hidden="true">
                    <i class="bi bi-camera-fill"></i>
                </span>
            </a>
            <p class="customer-page-profile__name">{{ $user->name }}</p>
        </div>
    @endif
</section>
