@extends('layouts.app')
@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="shop-checkout container">
      <h2 class="page-title">الشحن والدفع</h2>
      <div class="checkout-steps">
        <a href="{{ route('cart.index') }}" class="checkout-steps__item active completed">
          <span class="checkout-steps__item-number">01</span>
          <span class="checkout-steps__item-title">
            <span>حقيبة التسوق</span>
            <em>إدارة قائمة العناصر الخاصة بك</em>
          </span>
        </a>
        <a href="javascript:void(0)" class="checkout-steps__item active current">
          <span class="checkout-steps__item-number">02</span>
          <span class="checkout-steps__item-title">
            <span>الشحن والدفع</span>
            <em>إتمام عملية الشراء</em>
          </span>
        </a>
        <a href="javascript:void(0)" class="checkout-steps__item">
          <span class="checkout-steps__item-number">03</span>
          <span class="checkout-steps__item-title">
            <span>التأكيد</span>
            <em>مراجعة وإرسال طلبك</em>
          </span>
        </a>
      </div>
      <!-- عرض رسائل الخطأ والنجاح -->
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <form name="checkout-form" action="{{ route('cart.place.an.order') }}" method="POST">
        @csrf
        <div class="checkout-form">
          <div class="billing-info__wrapper">
            <div class="row">
              <div class="col-6">
                <h4>تفاصيل الشحن</h4>
              </div>
              <div class="col-6">
              </div>
            </div>
            @if($address)
                <div class="row">
                    <div class="col-md-12">
                        <div class="my-account_address-list-item">
                            <div class="my-account_address-item_detail">
                                <p>{{$address->name}}</p>
                                <p>{{$address->address}}</p>
                                <p>{{$address->landmark}}</p>
                                <p>{{$address->city}},{{$address->state}},{{$address->country}}</p>
                                <p>{{$address->zip}}</p>
                                <br/>
                                <p>{{$address->phone}}</p>
                            </div>
                        </div>
                    </div>
                </div>



            @else
            <div class="row mt-5">
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="name" required="" value="{{ old('name')}}">
                  <label for="name">الاسم الكامل *</label>
                  @error('name')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="phone" required="" value="{{ old('phone')}}">
                  <label for="phone">رقم الهاتف *</label>
                  @error('phone')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="zip" required="" value="{{ old('zip')}}">
                  <label for="zip">الرمز البريدي *</label>
                  @error('zip')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating mt-3 mb-3">
                  <input type="text" class="form-control" name="state" required="" value="{{ old('state')}}">
                  <label for="state">المحافظة *</label>
                  @error('state')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="city" required="" value="{{ old('city')}}">
                  <label for="city">المدينة *</label>
                  @error('city')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="address" required="" value="{{ old('address')}}">
                  <label for="address">رقم المنزل، اسم المبنى *</label>
                  @error('address')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="locality" required="" value="{{ old('locality')}}">
                  <label for="locality">اسم الشارع، المنطقة، الحي *</label>
                  @error('locality')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="landmark" required="" value="{{ old('landmark')}}">
                  <label for="landmark">معلم مميز *</label>
                  @error('landmark')<span class="text-danger">{{ $message }}</span> @enderror
                </div>
              </div>
            </div>
            @endif
          </div>
          <div class="checkout__totals-wrapper">
            <div class="sticky-content">
              <div class="checkout__totals">
                <h3>طلبك</h3>
                <table class="checkout-cart-items">
                  <thead>
                    <tr>
                      <th>المنتج</th>
                      <th class="text-end">المجموع الفرعي</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach(Cart::instance('cart')->content() as $item)
                    <tr>
                      <td>
                        {{ $item->name}} <strong class="text-muted" dir="ltr">x {{ $item->qty  }}</strong>
                        <x-order-item-options :options="$item->options" compact />
                      </td>
                      <td class="text-end">
                        {{ format_price($item->subtotal()) }}
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
                @if(Session::has('discounts'))
                    <table class="checkout-totals">
                        <tbody>
                            <tr>
                              <th>المجموع الفرعي</th>
                              <td class="text-end">{{ format_price(Cart::instance('cart')->subtotal()) }}</td>
                            </tr>
                            <tr>
                              <th>خصم {{ Session::get('coupon')['code'] }}</th>
                              <td class="text-end text-success">-{{ format_price(Session::get('discounts')['discount']) }}</td>
                            </tr>
                            <tr>
                              <th>المجموع الفرعي بعد الخصم</th>
                              <td class="text-end">{{ format_price(Session::get('discounts')['subtotal']) }}</td>
                            </tr>
                            <tr>
                              <th>الشحن</th>
                              <td class="text-end">شحن مجاني</td>
                            </tr>

                            <tr>
                              <th>الإجمالي</th>
                              <td class="text-end"><strong style="color: #d4a853; font-size: 1.2rem;">{{ format_price(Session::get('discounts')['total']) }}</strong></td>
                            </tr>
                          </tbody>
                  </table>
                @else
                <table class="checkout-totals">
                  <tbody>
                    <tr>
                      <th>المجموع الفرعي</th>
                      <td class="text-end">{{ format_price(Cart::instance('cart')->subtotal()) }}</td>
                    </tr>
                    <tr>
                      <th>الشحن</th>
                      <td class="text-end">شحن مجاني</td>
                    </tr>

                    <tr>
                      <th>الإجمالي</th>
                      <td class="text-end"><strong style="color: #d4a853; font-size: 1.2rem;">{{ format_price(Cart::instance('cart')->total()) }}</strong></td>
                    </tr>
                  </tbody>
                </table>
                @endif
              </div>
              <style>
                .payment-method-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 16px 20px;
                    margin-bottom: 15px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    background-color: #fff;
                }
                .payment-method-card:hover {
                    border-color: #d4a853;
                    box-shadow: 0 4px 12px rgba(212, 168, 83, 0.1);
                }
                .payment-method-card.active {
                    border-color: #d4a853;
                    background-color: #fdfaf3;
                }
                .payment-method-card .form-check-input {
                    margin-top: 0;
                    width: 22px;
                    height: 22px;
                    cursor: pointer;
                    flex-shrink: 0;
                }
                .payment-method-card .form-check-input:checked {
                    background-color: #d4a853;
                    border-color: #d4a853;
                }
                .payment-icon {
                    width: 28px;
                    height: 28px;
                    color: #4b5563;
                }
                .payment-text h5 {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 600;
                    color: #1f2937;
                }
                .payment-text p {
                    margin: 4px 0 0;
                    font-size: 13px;
                    color: #6b7280;
                }
                .bank-accounts-wrapper {
                    display: none;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 15px;
                    margin-bottom: 15px;
                    background-color: #fafafa;
                    animation: fadeIn 0.3s ease;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-5px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .bank-account-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 12px;
                    border-bottom: 1px solid #eaeaea;
                }
                .bank-account-item:last-child {
                    border-bottom: none;
                }
                .bank-account-item label {
                    cursor: pointer;
                    width: 100%;
                    margin: 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .bank-account-info h6 {
                    margin: 0 0 4px 0;
                    font-weight: 600;
                    font-size: 15px;
                }
                .bank-account-info span {
                    color: #6b7280;
                    font-size: 14px;
                }
              </style>

              <div class="checkout__payment-methods">
                <h4 class="mb-4 text-end fw-bold">أختر طريقة للدفع</h4>

                <!-- إيداع بنكي -->
                <label class="payment-method-card" for="mode1" id="card_mode1">
                    <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input" type="radio" name="mode" id="mode1" value="bank_transfer" required onchange="toggleBankAccounts()">
                        <div class="payment-text">
                            <h5>إيداع بنكي أو عن طريق صراف</h5>
                        </div>
                    </div>
                    <i data-lucide="credit-card" class="payment-icon"></i>
                </label>

                <!-- قائمة البنوك -->
                <div class="bank-accounts-wrapper" id="bankAccountsList">
                    <h6 class="mb-3 fw-bold text-center">أختر بنك أو صراف للإيداع</h6>
                    
                    <div class="bank-account-item">
                        <label for="bank1">
                            <div class="bank-account-info">
                                <h6>بنك الكريمي (حساب يمني)</h6>
                                <span dir="ltr">3049058534</span>
                            </div>
                            <input class="form-check-input" type="radio" name="bank_account" id="bank1" value="kuraimi_yer">
                        </label>
                    </div>
                    
                    <div class="bank-account-item">
                        <label for="bank2">
                            <div class="bank-account-info">
                                <h6>بنك الكريمي (حساب سعودي)</h6>
                                <span dir="ltr">3116019646</span>
                            </div>
                            <input class="form-check-input" type="radio" name="bank_account" id="bank2" value="kuraimi_sar">
                        </label>
                    </div>
                    
                    <div class="bank-account-item">
                        <label for="bank3">
                            <div class="bank-account-info">
                                <h6>بنك الكريمي (حساب دولار)</h6>
                                <span dir="ltr">3114677328</span>
                            </div>
                            <input class="form-check-input" type="radio" name="bank_account" id="bank3" value="kuraimi_usd">
                        </label>
                    </div>
                </div>

                <!-- المحفظة الإلكترونية -->
                <label class="payment-method-card" for="mode2" id="card_mode2">
                    <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input" type="radio" name="mode" id="mode2" value="e_wallet" required onchange="toggleBankAccounts()">
                        <div class="payment-text">
                            <h5>المحفظة الإلكترونية</h5>
                            <p class="text-primary"><i data-lucide="info" style="width:14px;height:14px"></i> دفع سريع وسهل</p>
                        </div>
                    </div>
                    <i data-lucide="smartphone" class="payment-icon"></i>
                </label>

                <!-- شراء بالتقسيط -->
                <label class="payment-method-card" for="mode3" id="card_mode3">
                    <div class="d-flex align-items-center gap-3">
                        <input class="form-check-input" type="radio" name="mode" id="mode3" value="installments" required onchange="toggleBankAccounts()">
                        <div class="payment-text">
                            <h5>شراء بالتقسيط</h5>
                            <p><i data-lucide="info" style="width:14px;height:14px"></i> طرق اقساط شهرية للطلبات التي تزيد عن 292,478.55 ريال جديد</p>
                        </div>
                    </div>
                    <i data-lucide="calendar" class="payment-icon"></i>
                </label>

                @error('mode')<span class="text-danger">{{ $message }}</span> @enderror

                <!-- رسالة تحقق طريقة الدفع -->
                <div id="paymentValidationError" class="payment-validation-error" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span id="paymentErrorText">يرجى اختيار طريقة الدفع لإكمال الطلب</span>
                    </div>
                </div>

                <div class="policy-text mt-3">
                  ستُستخدم بياناتك الشخصية لمعالجة طلبك ودعم تجربتك في هذا الموقع والأغراض الأخرى الموضحة في
                  <a href="{{ route('privacy') ?? '#' }}" target="_blank" class="text-danger text-decoration-none">سياسة الخصوصية</a> الخاصة بنا.
                </div>
              </div>
              <button type="submit" class="btn btn-golden btn-checkout w-100 py-3 mt-4 rounded-pill fs-5 fw-bold" onclick="return validatePaymentMethod()">تأكيد الطلب</button>
            </div>
          </div>
        </div>
      </form>
    </section>
  </main>

  <style>
    .payment-validation-error {
        background-color: #fff3f3;
        border: 1px solid #e74c3c;
        border-radius: 10px;
        padding: 14px 18px;
        margin-top: 15px;
        color: #c0392b;
        font-weight: 600;
        font-size: 14px;
        animation: shakeError 0.5s ease;
    }
    .payment-validation-error svg {
        flex-shrink: 0;
    }
    @keyframes shakeError {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
        20%, 40%, 60%, 80% { transform: translateX(4px); }
    }
  </style>

  <script>
    function toggleBankAccounts() {
      const mode1 = document.getElementById('mode1').checked;
      const bankAccountsList = document.getElementById('bankAccountsList');
      
      // Update active classes for cards
      document.querySelectorAll('.payment-method-card').forEach(card => card.classList.remove('active'));
      
      if (document.getElementById('mode1').checked) {
          document.getElementById('card_mode1').classList.add('active');
      }
      if (document.getElementById('mode2').checked) {
          document.getElementById('card_mode2').classList.add('active');
      }
      if (document.getElementById('mode3').checked) {
          document.getElementById('card_mode3').classList.add('active');
      }

      // Hide validation error when a payment method is selected
      hidePaymentError();

      // Show/Hide bank accounts list
      if (mode1) {
        bankAccountsList.style.display = 'block';
      } else {
        bankAccountsList.style.display = 'none';
        // uncheck bank accounts when hidden
        document.querySelectorAll('input[name="bank_account"]').forEach(radio => radio.checked = false);
      }
      
      try { lucide.createIcons(); } catch(e) {}
    }

    function showPaymentError(message) {
      const errorDiv = document.getElementById('paymentValidationError');
      const errorText = document.getElementById('paymentErrorText');
      errorText.textContent = message;
      errorDiv.style.display = 'block';
      // Scroll to the error message
      errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hidePaymentError() {
      const errorDiv = document.getElementById('paymentValidationError');
      if (errorDiv) {
        errorDiv.style.display = 'none';
      }
    }

    function validatePaymentMethod() {
      const paymentMethods = document.querySelectorAll('input[name="mode"]');
      let isSelected = false;
      let selectedValue = null;

      paymentMethods.forEach(function(method) {
        if (method.checked) {
          isSelected = true;
          selectedValue = method.value;
        }
      });

      if (!isSelected) {
        showPaymentError('يرجى اختيار طريقة الدفع لإكمال الطلب');
        return false;
      }

      if (selectedValue === 'bank_transfer') {
          const bankSelected = document.querySelector('input[name="bank_account"]:checked');
          if (!bankSelected) {
              showPaymentError('يرجى إختيار الحساب البنكي للإيداع');
              return false;
          }
      }

      hidePaymentError();
      return true;
    }

    // Backup: prevent form submission via the form's submit event
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form[name="checkout-form"]');
      if (form) {
        form.addEventListener('submit', function(e) {
          if (!validatePaymentMethod()) {
            e.preventDefault();
            return false;
          }
        });
      }

      // Hide error when bank account is selected
      document.querySelectorAll('input[name="bank_account"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
          hidePaymentError();
        });
      });
    });
  </script>
@endsection
