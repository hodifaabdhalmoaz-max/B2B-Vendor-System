@extends('layouts.app')

@section('title', 'سياسة الخصوصية - متجر الأطفال')
@section('description', 'سياسة الخصوصية الخاصة بمتجر الأطفال - نحن نحترم خصوصيتك ونحمي بياناتك الشخصية')

@section('content')
<!-- Hero Section -->
<section class="info-hero">
    <div class="container position-relative">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="breadcrumb-modern">
                    <a href="{{ route('home.index') }}">
                        <i data-lucide="home" style="width: 16px; height: 16px;"></i>
                        الرئيسية
                    </a>
                    <i data-lucide="chevron-left" style="width: 16px; height: 16px; color: #a0aec0;"></i>
                    <span style="color: #2d3748; font-weight: 600;">سياسة الخصوصية</span>
                </div>

                <h1 class="display-4 fw-bold mb-4">سياسة الخصوصية</h1>
                <p class="lead mb-0">نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية وفقاً لأعلى معايير الأمان</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Data Collection Section -->
                <div class="info-card">
                    <div class="info-icon">
                        <i data-lucide="database" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="info-section-title">
                        <i data-lucide="info" style="width: 20px; height: 20px; color: var(--page-brand-start);"></i>
                        المعلومات التي نجمعها
                    </h3>
                    <div class="info-content">
                        <p class="mb-4">نقوم بجمع المعلومات التالية لتحسين خدماتنا وضمان تجربة تسوق آمنة:</p>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                <div><strong>المعلومات الشخصية:</strong> الاسم، البريد الإلكتروني، رقم الهاتف، والعنوان</div>
                            </li>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                <div><strong>معلومات الطلبات:</strong> تفاصيل المنتجات المشتراة وتاريخ الطلبات</div>
                            </li>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                <div><strong>معلومات تقنية:</strong> عنوان IP، نوع المتصفح، ونظام التشغيل</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Data Usage Section -->
                <div class="info-card">
                    <div class="info-icon">
                        <i data-lucide="settings" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="info-section-title">
                        <i data-lucide="target" style="width: 20px; height: 20px; color: var(--page-brand-start);"></i>
                        كيف نستخدم معلوماتك
                    </h3>
                    <div class="info-content">
                        <p class="mb-4">نستخدم المعلومات المجمعة للأغراض التالية:</p>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="shopping-cart" class="text-primary mt-1" style="width: 18px; height: 18px;"></i>
                                <div>معالجة وتنفيذ طلباتك بدقة وسرعة</div>
                            </li>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i data-lucide="headphones" class="text-primary mt-1" style="width: 18px; height: 18px;"></i>
                                <div>تقديم خدمة عملاء متميزة والرد على استفساراتك</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="info-cta">
                    <h4 class="mb-3">
                        <i data-lucide="help-circle" style="width: 24px; height: 24px; margin-left: 10px;"></i>
                        هل لديك أسئلة حول سياسة الخصوصية؟
                    </h4>
                    <p class="mb-4">فريقنا جاهز للإجابة على جميع استفساراتك وتوضيح أي نقطة تحتاج لمزيد من التفاصيل</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="mailto:hodifaabdhalmoaz@gmail.com" class="btn-modern-white">
                            <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                            راسلنا عبر البريد
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
    // Privacy policy scripts
</script>
@endpush
