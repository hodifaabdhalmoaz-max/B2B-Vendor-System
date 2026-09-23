@extends('layouts.app')

@section('title', __('messages.footer_returns') . ' - متجر الأطفال')
@section('description', __('messages.returns_subtitle'))

@section('content')
<!-- Hero Section -->
<section class="info-hero">
    <div class="container position-relative">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="breadcrumb-modern">
                    <a href="{{ route('home.index') }}">
                        <i class="bi bi-house-door" style="font-size: 16px;"></i>
                        {{ __('messages.home') }}
                    </a>
                    <i class="bi bi-chevron-left" style="font-size: 16px; color: #a0aec0;"></i>
                    <span style="color: #2d3748; font-weight: 600;">{{ __('messages.footer_returns') }}</span>
                </div>

                <h1 class="display-4 fw-bold mb-4">{{ __('messages.footer_returns') }}</h1>
                <p class="lead mb-0">{{ __('messages.returns_subtitle') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Return Policy -->
                <div class="info-card mb-4">
                    <div class="info-icon">
                        <i class="bi bi-shield-exclamation text-white" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="info-section-title">
                        <i class="bi bi-info-circle" style="font-size: 20px; color: var(--gold-start, #f0c14b);"></i>
                        {{ __('messages.return_policy_title') }}
                    </h3>
                    <div class="info-content">
                        <p class="mb-4">{{ __('messages.return_policy_content') }}</p>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i class="bi bi-check-circle mt-1" style="font-size: 18px; color: var(--gold-start, #f0c14b);"></i>
                                <div>{{ __('messages.return_policy_point_1') }}</div>
                            </li>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i class="bi bi-check-circle mt-1" style="font-size: 18px; color: var(--gold-start, #f0c14b);"></i>
                                <div>{{ __('messages.return_policy_point_2') }}</div>
                            </li>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <i class="bi bi-check-circle mt-1" style="font-size: 18px; color: var(--gold-start, #f0c14b);"></i>
                                <div>{{ __('messages.return_policy_point_3') }}</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Return Process -->
                <div class="info-card mb-4">
                    <div class="info-icon">
                        <i class="bi bi-arrow-repeat text-white" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="info-section-title d-flex justify-content-center border-0 mb-4 pb-0" style="color: var(--gold-start, #f0c14b);">
                        {{ __('messages.return_process_title') }}
                    </h3>
                    <div class="info-content">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="d-flex bg-white p-3 rounded shadow-sm border border-light h-100 align-items-start" style="border-radius: 12px;">
                                    <div class="flex-shrink-0">
                                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.2rem; background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">
                                            <span class="fw-bold">1</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3" style="margin-right: 15px;"> <!-- Added margin right for RTL -->
                                        <h5 class="mb-1" style="color: #1e293b;">{{ __('messages.return_step_1_title') }}</h5>
                                        <p class="text-muted small mb-0">{{ __('messages.return_step_1_content') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="d-flex bg-white p-3 rounded shadow-sm border border-light h-100 align-items-start" style="border-radius: 12px;">
                                    <div class="flex-shrink-0">
                                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.2rem; background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">
                                            <span class="fw-bold">2</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3" style="margin-right: 15px;">
                                        <h5 class="mb-1" style="color: #1e293b;">{{ __('messages.return_step_2_title') }}</h5>
                                        <p class="text-muted small mb-0">{{ __('messages.return_step_2_content') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4 mb-md-0">
                                <div class="d-flex bg-white p-3 rounded shadow-sm border border-light h-100 align-items-start" style="border-radius: 12px;">
                                    <div class="flex-shrink-0">
                                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.2rem; background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">
                                            <span class="fw-bold">3</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3" style="margin-right: 15px;">
                                        <h5 class="mb-1" style="color: #1e293b;">{{ __('messages.return_step_3_title') }}</h5>
                                        <p class="text-muted small mb-0">{{ __('messages.return_step_3_content') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex bg-white p-3 rounded shadow-sm border border-light h-100 align-items-start" style="border-radius: 12px;">
                                    <div class="flex-shrink-0">
                                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.2rem; background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">
                                            <span class="fw-bold">4</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3" style="margin-right: 15px;">
                                        <h5 class="mb-1" style="color: #1e293b;">{{ __('messages.return_step_4_title') }}</h5>
                                        <p class="text-muted small mb-0">{{ __('messages.return_step_4_content') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact for Returns -->
                <div class="info-card mb-4">
                    <div class="info-icon" style="background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">
                        <i class="bi bi-headset" style="font-size: 24px; color: white;"></i>
                    </div>
                    <h3 class="info-section-title">
                        <i class="bi bi-chat-square-text" style="font-size: 20px; color: var(--gold-start, #f0c14b);"></i>
                        {{ __('messages.return_contact_title') }}
                    </h3>
                    <div class="info-content">
                        <p class="mb-4">{{ __('messages.return_contact_content') }}</p>

                        <div class="row text-center mt-4">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="p-3 border rounded shadow-sm hover-gold-border transition">
                                    <i class="bi bi-envelope mb-2" style="font-size: 24px; color: var(--gold-start, #f0c14b);"></i>
                                    <br>
                                    <strong>{{ __('messages.contact_email') }}</strong><br>
                                    <a href="mailto:hodifaabdhalmoaz@gmail.com" class="text-decoration-none">hodifaabdhalmoaz@gmail.com</a>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="p-3 border rounded shadow-sm hover-gold-border transition">
                                    <i class="bi bi-telephone mb-2" style="font-size: 24px; color: var(--gold-start, #f0c14b);"></i>
                                    <br>
                                    <strong>{{ __('messages.contact_phone_number') }}</strong><br>
                                    <a href="tel:+967777548421" class="text-decoration-none" style="direction: ltr; display: inline-block;">777548421 967+</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded shadow-sm hover-gold-border transition">
                                    <i class="bi bi-whatsapp mb-2" style="font-size: 24px; color: #128C7E;"></i>
                                    <br>
                                    <strong>{{ __('messages.whatsapp_us') }}</strong><br>
                                    <a href="https://wa.me/967777548421" target="_blank" class="text-decoration-none text-success" style="direction: ltr; display: inline-block;">777548421 967+</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="info-cta mt-5">
                    <h4 class="mb-3">
                        <i class="bi bi-question-circle" style="font-size: 24px; margin-left: 10px; color: var(--gold-start, #f0c14b);"></i>
                        {{ __('messages.need_help_returns') }}
                    </h4>
                    <p class="mb-4">{{ __('messages.returns_help_text') }}</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('contact') }}" class="btn-modern-white text-decoration-none border shadow-sm">
                            <i class="bi bi-envelope" style="font-size: 18px; color: var(--gold-start, #f0c14b);"></i>
                            {{ __('messages.contact_us') }}
                        </a>
                        <a href="https://wa.me/967777548421" target="_blank" class="btn-modern-white text-decoration-none border shadow-sm" style="color: #128C7E;">
                            <i class="bi bi-whatsapp" style="font-size: 18px; color: #128C7E;"></i>
                            {{ __('messages.whatsapp_us') }}
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
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endpush