@extends('layouts.app')

@section('title', __('messages.footer_shipping') . ' - متجر الأطفال')
@section('description', __('messages.shipping_subtitle'))

@section('content')
<!-- Hero Section -->
<section class="info-hero">
    <div class="container position-relative">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="breadcrumb-modern">
                    <a href="{{ route('home.index') }}">
                        <i data-lucide="home" style="width: 16px; height: 16px;"></i>
                        {{ __('messages.home') }}
                    </a>
                    <i data-lucide="chevron-left" style="width: 16px; height: 16px; color: #a0aec0;"></i>
                    <span style="color: #2d3748; font-weight: 600;">{{ __('messages.footer_shipping') }}</span>
                </div>

                <h1 class="display-4 fw-bold mb-4">{{ __('messages.footer_shipping') }}</h1>
                <p class="lead mb-0">{{ __('messages.shipping_subtitle') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Shipping Areas -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-4">
                        <div class="info-card h-100">
                            <div class="info-icon">
                                <i data-lucide="map-pin" style="width: 24px; height: 24px;"></i>
                            </div>
                            <h3 class="info-section-title">
                                <i data-lucide="info" style="width: 20px; height: 20px; color: var(--page-brand-start);"></i>
                                {{ __('messages.shipping_local_title') }}
                            </h3>
                            <div class="info-content">
                                <ul class="list-unstyled">
                                    <li class="d-flex align-items-start gap-3 mb-3">
                                        <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                        <div>{{ __('messages.shipping_local_point_1') }}</div>
                                    </li>
                                    <li class="d-flex align-items-start gap-3 mb-3">
                                        <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                        <div>{{ __('messages.shipping_local_point_2') }}</div>
                                    </li>
                                    <li class="d-flex align-items-start gap-3 mb-3">
                                        <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                        <div>{{ __('messages.shipping_local_point_3') }}</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="info-card h-100">
                            <div class="info-icon">
                                <i data-lucide="truck" style="width: 24px; height: 24px;"></i>
                            </div>
                            <h3 class="info-section-title">
                                <i data-lucide="info" style="width: 20px; height: 20px; color: var(--page-brand-start);"></i>
                                {{ __('messages.shipping_national_title') }}
                            </h3>
                            <div class="info-content">
                                <ul class="list-unstyled">
                                    <li class="d-flex align-items-start gap-3 mb-3">
                                        <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                        <div>{{ __('messages.shipping_national_point_1') }}</div>
                                    </li>
                                    <li class="d-flex align-items-start gap-3 mb-3">
                                        <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                        <div>{{ __('messages.shipping_national_point_2') }}</div>
                                    </li>
                                    <li class="d-flex align-items-start gap-3 mb-3">
                                        <i data-lucide="check-circle" class="text-success mt-1" style="width: 18px; height: 18px;"></i>
                                        <div>{{ __('messages.shipping_national_point_3') }}</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Process -->
                <div class="info-card mb-4">
                    <div class="info-icon">
                        <i data-lucide="git-merge" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="info-section-title d-flex justify-content-center border-0 mb-4 pb-0" style="color: var(--page-brand-start);">
                        {{ __('messages.shipping_process_title') }}
                    </h3>
                    <div class="info-content">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="text-center">
                                    <div class="btn-golden rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                        <i data-lucide="shopping-cart" style="color: white;"></i>
                                    </div>
                                    <h5>{{ __('messages.shipping_step_1_title') }}</h5>
                                    <p class="text-dark fw-medium small">{{ __('messages.shipping_step_1_content') }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="text-center">
                                    <div class="btn-golden rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                        <i data-lucide="check-circle-2" style="color: white;"></i>
                                    </div>
                                    <h5>{{ __('messages.shipping_step_2_title') }}</h5>
                                    <p class="text-dark fw-medium small">{{ __('messages.shipping_step_2_content') }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="text-center">
                                    <div class="btn-golden rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                        <i data-lucide="package" style="color: white;"></i>
                                    </div>
                                    <h5>{{ __('messages.shipping_step_3_title') }}</h5>
                                    <p class="text-dark fw-medium small">{{ __('messages.shipping_step_3_content') }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="text-center">
                                    <div class="btn-golden rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                        <i data-lucide="home" style="color: white;"></i>
                                    </div>
                                    <h5>{{ __('messages.shipping_step_4_title') }}</h5>
                                    <p class="text-dark fw-medium small">{{ __('messages.shipping_step_4_content') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Rates -->
                <div class="info-card mb-4">
                    <div class="info-icon">
                        <i data-lucide="calculator" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 class="info-section-title">
                        <i data-lucide="credit-card" style="width: 20px; height: 20px; color: var(--page-brand-start);"></i>
                        {{ __('messages.shipping_rates_title') }}
                    </h3>
                    <div class="info-content">
                        <div class="table-responsive" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="btn-golden text-white">
                                    <tr>
                                        <th class="py-3 px-4 border-0 text-white">{{ __('messages.shipping_location') }}</th>
                                        <th class="py-3 px-4 border-0 text-white">{{ __('messages.shipping_time') }}</th>
                                        <th class="py-3 px-4 border-0 text-white">{{ __('messages.shipping_cost') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-3 px-4">
                                            <div class="d-flex align-items-center gap-2"><i data-lucide="map-pin" style="width: 16px; height: 16px;" class="text-primary"></i> {{ __('messages.shipping_aden') }}</div>
                                        </td>
                                        <td class="py-3 px-4">{{ __('messages.shipping_1_2_days') }}</td>
                                        <td class="py-3 px-4"><span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #198754; color: white;">{{ __('messages.shipping_free') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4">
                                            <div class="d-flex align-items-center gap-2"><i data-lucide="map-pin" style="width: 16px; height: 16px;" class="text-secondary"></i> {{ __('messages.shipping_sanaa') }}</div>
                                        </td>
                                        <td class="py-3 px-4">{{ __('messages.shipping_2_3_days') }}</td>
                                        <td class="py-3 px-4"><span class="fw-bold">{{ __('messages.shipping_1000_rial') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4">
                                            <div class="d-flex align-items-center gap-2"><i data-lucide="map-pin" style="width: 16px; height: 16px;" class="text-secondary"></i> {{ __('messages.shipping_taiz') }}</div>
                                        </td>
                                        <td class="py-3 px-4">{{ __('messages.shipping_2_3_days') }}</td>
                                        <td class="py-3 px-4"><span class="fw-bold">{{ __('messages.shipping_1000_rial') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4">
                                            <div class="d-flex align-items-center gap-2"><i data-lucide="map-pin" style="width: 16px; height: 16px;" class="text-secondary"></i> {{ __('messages.shipping_hodeidah') }}</div>
                                        </td>
                                        <td class="py-3 px-4">{{ __('messages.shipping_3_4_days') }}</td>
                                        <td class="py-3 px-4"><span class="fw-bold">{{ __('messages.shipping_1500_rial') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 border-0">
                                            <div class="d-flex align-items-center gap-2"><i data-lucide="map-pin" style="width: 16px; height: 16px;" class="text-secondary"></i> {{ __('messages.shipping_other_cities') }}</div>
                                        </td>
                                        <td class="py-3 px-4 border-0">{{ __('messages.shipping_3_5_days') }}</td>
                                        <td class="py-3 px-4 border-0"><span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #0dcaf0; color: #000;">{{ __('messages.shipping_contact_us') }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="info-cta">
                    <h4 class="mb-3">
                        <i data-lucide="help-circle" style="width: 24px; height: 24px; margin-left: 10px;"></i>
                        {{ __('messages.shipping_questions') }}
                    </h4>
                    <p class="mb-4">{{ __('messages.shipping_questions_text') }}</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('contact') }}" class="btn-modern-white text-decoration-none">
                            <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                            {{ __('messages.contact_us') }}
                        </a>
                        <a href="https://wa.me/967777548421" target="_blank" class="btn-modern-white text-decoration-none" style="color: #128C7E;">
                            <i data-lucide="phone" style="width: 18px; height: 18px;"></i>
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
    // Specific shipping page scripts
</script>
@endpush