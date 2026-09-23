@extends('layouts.base')

@section('title', __('messages.checkout') . ' - ' . __('messages.payment'))

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">{{ __('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">{{ __('messages.cart') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('checkout.index') }}">{{ __('messages.checkout') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('messages.payment') }}</li>
        </ol>
    </nav>

    <!-- Checkout Steps -->
    <div class="checkout-steps mb-5">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <div class="step-item completed">
                    <div class="step-number">1</div>
                    <div class="step-title">{{ __('messages.cart') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <div class="step-item completed">
                    <div class="step-number">2</div>
                    <div class="step-title">{{ __('messages.checkout_billing_details') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="step-item active">
                    <div class="step-number">3</div>
                    <div class="step-title">{{ __('messages.payment') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-title">{{ __('messages.confirmation') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payment Methods -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-4">{{ __('messages.checkout_payment_method') }}</h3>

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form action="{{ route('checkout.process-payment') }}" method="POST" id="payment-form">
                        @csrf

                        <div class="mb-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="whatsapp_payment" value="whatsapp" checked>
                                <label class="form-check-label" for="whatsapp_payment">
                                    <i class="fab fa-whatsapp text-success me-2"></i>{{ __('messages.whatsapp_payment') }}
                                </label>
                                <div class="form-text">{{ __('messages.whatsapp_payment_description') }}</div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                <label class="form-check-label" for="bank_transfer">
                                    <i class="fas fa-university me-2"></i>{{ __('messages.bank_transfer') }}
                                </label>
                                <div class="form-text">{{ __('messages.bank_transfer_description') }}</div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cod">
                                <label class="form-check-label" for="cash_on_delivery">
                                    <i class="fas fa-money-bill-wave me-2"></i>{{ __('messages.cash_on_delivery') }}
                                </label>
                                <div class="form-text">{{ __('messages.cash_on_delivery_description') }}</div>
                            </div>
                        </div>

                        <!-- WhatsApp Payment Details -->
                        <div id="whatsapp_payment_details" class="payment-details mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="mb-3">{{ __('messages.whatsapp_payment_instructions') }}</h5>
                                    <p>{{ __('messages.whatsapp_payment_steps') }}</p>
                                    <ol>
                                        <li>{{ __('messages.whatsapp_payment_step_1') }}</li>
                                        <li>{{ __('messages.whatsapp_payment_step_2') }}</li>
                                        <li>{{ __('messages.whatsapp_payment_step_3') }}</li>
                                        <li>{{ __('messages.whatsapp_payment_step_4') }}</li>
                                    </ol>
                                    <div class="text-center mt-3">
                                        <a href="https://wa.me/967718706242?text={{ urlencode(__('messages.whatsapp_payment_message', ['order_id' => session('order_id', 'TEMP-' . time()), 'amount' => Cart::instance('cart')->total()])) }}" target="_blank" class="btn btn-success">
                                            <i class="fab fa-whatsapp me-2"></i>{{ __('messages.contact_on_whatsapp') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Transfer Details -->
                        <div id="bank_transfer_details" class="payment-details mb-4 d-none">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="mb-3">{{ __('messages.bank_transfer_instructions') }}</h5>
                                    <p>{{ __('messages.bank_transfer_steps') }}</p>
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>{{ __('messages.bank_name') }}:</strong> اليمن الدولي</p>
                                        <p class="mb-1"><strong>{{ __('messages.account_name') }}:</strong> حذيفة عبدالمعز الحذيفي</p>
                                        <p class="mb-1"><strong>{{ __('messages.account_number') }}:</strong> 123456789</p>
                                        <p class="mb-0"><strong>{{ __('messages.reference') }}:</strong> {{ session('order_id', 'TEMP-' . time()) }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="transaction_id" class="form-label">{{ __('messages.transaction_id') }}</label>
                                        <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                                        <div class="form-text">{{ __('messages.transaction_id_description') }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="transaction_date" class="form-label">{{ __('messages.transaction_date') }}</label>
                                        <input type="date" class="form-control" id="transaction_date" name="transaction_date">
                                    </div>
                                    <div class="mb-3">
                                        <label for="payment_proof" class="form-label">{{ __('messages.payment_proof') }}</label>
                                        <input type="file" class="form-control" id="payment_proof" name="payment_proof">
                                        <div class="form-text">{{ __('messages.payment_proof_description') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cash on Delivery Details -->
                        <div id="cash_on_delivery_details" class="payment-details mb-4 d-none">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="mb-3">{{ __('messages.cash_on_delivery_instructions') }}</h5>
                                    <p>{{ __('messages.cash_on_delivery_steps') }}</p>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cod_agreement" name="cod_agreement">
                                        <label class="form-check-label" for="cod_agreement">
                                            {{ __('messages.cash_on_delivery_agreement') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('checkout.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>{{ __('messages.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.place_order') }} <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-4">{{ __('messages.checkout_order_summary') }}</h3>

                    <div class="order-summary">
                        @foreach(Cart::instance('cart')->content() as $item)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('storage/' . $item->model->image) }}" alt="{{ $item->name }}" class="img-fluid rounded" width="60">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $item->name }}</h6>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">{{ $item->qty }} x {{ $item->price }}</span>
                                    <span>{{ $item->subtotal }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <hr>

                        <div class="summary-item d-flex justify-content-between mb-2">
                            <span>{{ __('messages.cart_subtotal') }}</span>
                            <span>{{ Cart::instance('cart')->subtotal() }}</span>
                        </div>

                        @if(session()->has('coupon'))
                        <div class="summary-item d-flex justify-content-between mb-2">
                            <span>{{ __('messages.cart_discount') }} ({{ session('coupon')['code'] }})</span>
                            <span>-{{ session('coupon')['discount'] }}</span>
                        </div>
                        @endif



                        <div class="summary-item d-flex justify-content-between mb-2">
                            <span>{{ __('messages.cart_shipping') }}</span>
                            <span>{{ session('shipping_cost', 0) }}</span>
                        </div>

                        <hr>

                        <div class="summary-item d-flex justify-content-between mb-0">
                            <span class="fw-bold">{{ __('messages.cart_total') }}</span>
                            <span class="fw-bold">{{ Cart::instance('cart')->total() + session('shipping_cost', 0) - (session()->has('coupon') ? session('coupon')['discount'] : 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">{{ __('messages.need_help') }}</h5>
                    <p>{{ __('messages.payment_support_description') }}</p>
                    <div class="d-grid gap-2">
                        <a href="https://wa.me/967718706242" target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp me-2"></i>{{ __('messages.contact_support') }}
                        </a>
                        <a href="tel:+967777548421" class="btn btn-outline-primary">
                            <i class="fas fa-phone-alt me-2"></i>777548421 967+
                        </a>
                        <a href="mailto:hodifaabdhalmoaz@gmail.com" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>{{ __('messages.email_support') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const whatsappDetails = document.getElementById('whatsapp_payment_details');
        const bankDetails = document.getElementById('bank_transfer_details');
        const codDetails = document.getElementById('cash_on_delivery_details');

        function togglePaymentDetails() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;

            whatsappDetails.classList.add('d-none');
            bankDetails.classList.add('d-none');
            codDetails.classList.add('d-none');

            if (selectedMethod === 'whatsapp') {
                whatsappDetails.classList.remove('d-none');
            } else if (selectedMethod === 'bank_transfer') {
                bankDetails.classList.remove('d-none');
            } else if (selectedMethod === 'cod') {
                codDetails.classList.remove('d-none');
            }
        }

        paymentMethods.forEach(method => {
            method.addEventListener('change', togglePaymentDetails);
        });

        // Initialize display
        togglePaymentDetails();
    });
</script>
@endsection