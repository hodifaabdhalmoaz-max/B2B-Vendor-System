@extends('layouts.app')

@section('title', 'الشروط والأحكام - متجر الأطفال')
@section('description', 'الشروط والأحكام الخاصة بمتجر الأطفال - اقرأ شروط الاستخدام والأحكام قبل التسوق')

@section('content')
<!-- Hero Section -->
<section class="info-hero">
    <div class="container position-relative">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="breadcrumb-modern">
                    <a href="{{ route('home.index') }}">
                        <i data-lucide="home" style="width: 18px; height: 18px;"></i>
                        الرئيسية
                    </a>
                    <i data-lucide="chevron-left" style="width: 16px; height: 16px; color: #a0aec0;"></i>
                    <span style="color: #1a365d; font-weight: 700;">الشروط والأحكام</span>
                </div>

                <h1 class="display-4 fw-bold mb-4">الشروط والأحكام</h1>
                <p class="lead mb-0">اقرأ شروط الاستخدام والأحكام الخاصة بمتجرنا لضمان تجربة تسوق آمنة ومريحة</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- General Terms Section -->
                <div class="info-card">
                    <div class="info-icon">
                        <i data-lucide="file-text" style="width: 28px; height: 28px;"></i>
                    </div>
                    <h3 class="info-section-title">
                        <i data-lucide="info" style="width: 22px; height: 22px; color: var(--page-brand-start);"></i>
                        الشروط العامة للاستخدام
                    </h3>
                    <div class="info-content">
                        <p class="mb-4">باستخدام موقعنا الإلكتروني، فإنك توافق على الالتزام بهذه الشروط والأحكام:</p>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="user-check" class="text-success mt-1" style="width: 20px; height: 20px;"></i>
                                <div><strong>العمر القانوني:</strong> يجب أن تكون بالغاً من العمر 18 عاماً أو أكثر</div>
                            </li>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="shield-check" class="text-success mt-1" style="width: 20px; height: 20px;"></i>
                                <div><strong>دقة المعلومات:</strong> تتعهد بتقديم معلومات صحيحة ودقيقة</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Products and Services Section -->
                <div class="info-card">
                    <div class="info-icon">
                        <i data-lucide="package" style="width: 28px; height: 28px;"></i>
                    </div>
                    <h3 class="info-section-title">
                        <i data-lucide="shopping-bag" style="width: 22px; height: 22px; color: var(--page-brand-start);"></i>
                        المنتجات والخدمات
                    </h3>
                    <div class="info-content">
                        <p class="mb-4">شروط وأحكام المنتجات والخدمات المقدمة في متجرنا:</p>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="check-circle" class="text-warning mt-1" style="width: 20px; height: 20px;"></i>
                                <div>جميع المنتجات خاضعة للتوفر في المخزون</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="info-cta">
                    <h4 class="mb-3">
                        <i data-lucide="help-circle" style="width: 24px; height: 24px; margin-left: 10px;"></i>
                        هل لديك أسئلة حول الشروط والأحكام؟
                    </h4>
                    <p class="mb-4">فريقنا القانوني جاهز للإجابة على جميع استفساراتك وتوضيح أي بند تحتاج لمزيد من التفاصيل</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="mailto:hodifaabdhalmoaz@gmail.com" class="btn-modern-white">
                            <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                            راسلنا عبر البريد
                        </a>
                        <a href="{{ route('contact') }}" class="btn-modern-white">
                            <i data-lucide="message-square" style="width: 18px; height: 18px;"></i>
                            صفحة الاتصال
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Terms scripts
</script>
@endpush
