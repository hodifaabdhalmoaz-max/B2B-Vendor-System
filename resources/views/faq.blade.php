@extends('layouts.app')

@section('title', 'الأسئلة الشائعة - متجر الأطفال')
@section('description', 'إجابات سريعة لأهم الاستفسارات حول منتجاتنا وخدماتنا')

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
                    <span style="color: #2d3748; font-weight: 600;">الأسئلة الشائعة</span>
                </div>

                <h1 class="display-4 fw-bold mb-4">الأسئلة الشائعة</h1>
                <p class="lead mb-0">كل ما تحتاج لمعرفته حول خدماتنا ومنتجاتنا في مكان واحد</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="accordion accordion-modern" id="faqAccordion">
                    <!-- FAQ 1 -->
                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i data-lucide="shopping-cart" class="me-3 text-warning"></i>
                                كيف يمكنني تقديم طلب؟
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                يمكنك تقديم طلب بسهولة من خلال تصفح منتجاتنا، إضافة المنتجات المرغوبة إلى السلة، ثم إكمال عملية الدفع. نوفر عدة طرق دفع آمنة ومريحة.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i data-lucide="truck" class="me-3 text-warning"></i>
                                ما هي مدة التوصيل؟
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                نوفر خدمة توصيل سريعة خلال 1-3 أيام عمل داخل المدن الرئيسية، و3-5 أيام للمناطق الأخرى. نرسل لك رسالة تأكيد مع رقم التتبع فور شحن طلبك.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i data-lucide="credit-card" class="me-3 text-warning"></i>
                                ما هي طرق الدفع المتاحة؟
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                نقبل الدفع عند الاستلام، التحويل البنكي، والدفع الإلكتروني عبر المحافظ المحلية. جميع المعاملات آمنة ومحمية بأحدث تقنيات التشفير.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i data-lucide="rotate-ccw" class="me-3 text-warning"></i>
                                هل يمكنني إرجاع المنتج؟
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                نعم، يمكنك إرجاع المنتج خلال 3 أيام من تاريخ الاستلام بشرط أن يكون في حالته الأصلية وغير مستخدم. تفضل بزيارة صفحة سياسة الإرجاع لمزيد من التفاصيل.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i data-lucide="shield-check" class="me-3 text-warning"></i>
                                هل المنتجات آمنة للأطفال؟
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                جميع منتجاتنا تخضع لاختبارات صارمة وتلتزم بمعايير السلامة الدولية. نختار منتجات من علامات تجارية موثوقة ومعتمدة لضمان سلامة أطفالكم.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Still Have Questions CTA -->
                <div class="info-cta">
                    <h4 class="mb-3">لم تجد إجابة لسؤالك؟</h4>
                    <p class="mb-4 text-white opacity-75">لا تتردد في التواصل معنا! فريق خدمة العملاء جاهز لمساعدتك على مدار الساعة</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('contact') }}" class="btn-modern-white">
                            <i data-lucide="mail" class="me-2"></i>تواصل معنا
                        </a>
                        <a href="https://wa.me/967777548421" target="_blank" class="btn-modern-white">
                            <i data-lucide="message-circle" class="me-2"></i>واتساب
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
    // FAQ page scripts
</script>
@endpush