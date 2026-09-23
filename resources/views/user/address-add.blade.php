@extends('layouts.app')

@section('content')
<main class="pt-90">
    
    @include('user.components.dashboard-header', ['title' => 'إضافة عنوان جديد', 'description' => 'أضف عنوان توصيل جديد لتسهيل عملية الشراء'])
    <section class="my-account container">

        <div class="row">
            <div class="col-lg-3 d-none d-md-block">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">تفاصيل العنوان الجديد</h5>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <form method="POST" action="{{ route('user.address.store') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="name" class="form-label fw-600">اسم المستلم</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-person text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 rounded-end-4 py-2 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="أدخل اسم المستلم" required>
                                        </div>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label for="phone" class="form-label fw-600">رقم الهاتف</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-telephone text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 rounded-end-4 py-2 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="أدخل رقم الهاتف" required>
                                        </div>
                                        @error('phone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="address" class="form-label fw-600">العنوان (الشارع / رقم المنزل)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-geo-alt text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 rounded-end-4 py-2 @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" placeholder="مثال: شارع الملك خالد، بناية 10" required>
                                        </div>
                                        @error('address')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label for="locality" class="form-label fw-600">المنطقة / الحي</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-building text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 rounded-end-4 py-2 @error('locality') is-invalid @enderror" id="locality" name="locality" value="{{ old('locality') }}" placeholder="أدخل الحي أو المنطقة" required>
                                        </div>
                                        @error('locality')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label for="city" class="form-label fw-600">المدينة</label>
                                        <input type="text" class="form-control bg-light border-0 rounded-4 py-2 @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" placeholder="المدينة" required>
                                        @error('city')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <label for="state" class="form-label fw-600">المنطقة / المحافظة</label>
                                        <input type="text" class="form-control bg-light border-0 rounded-4 py-2 @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" placeholder="المنطقة" required>
                                        @error('state')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <label for="country" class="form-label fw-600">الدولة</label>
                                        <input type="text" class="form-control bg-light border-0 rounded-4 py-2 @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', 'اليمن') }}" placeholder="الدولة" required>
                                        @error('country')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8 mb-4">
                                        <label for="landmark" class="form-label fw-600">أقرب معلم (اختياري)</label>
                                        <input type="text" class="form-control bg-light border-0 rounded-4 py-2 @error('landmark') is-invalid @enderror" id="landmark" name="landmark" value="{{ old('landmark') }}" placeholder="مثال: بجانب مسجد التقوى">
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <label for="zip" class="form-label fw-600">الرمز البريدي</label>
                                        <input type="text" class="form-control bg-light border-0 rounded-4 py-2 @error('zip') is-invalid @enderror" id="zip" name="zip" value="{{ old('zip') }}" placeholder="12345" required>
                                        @error('zip')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-600 d-block">نوع العنوان</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="type" id="type_home" value="home" checked>
                                                <label class="form-check-label" for="type_home">منزل</label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="type" id="type_office" value="office">
                                                <label class="form-check-label" for="type_office">عمل</label>
                                            </div>
                                            <div class="form-check custom-radio">
                                                <input class="form-check-input" type="radio" name="type" id="type_other" value="other">
                                                <label class="form-check-label" for="type_other">أخرى</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch custom-switch">
                                            <input class="form-check-input" type="checkbox" id="isdefault" name="isdefault" value="1">
                                            <label class="form-check-label fw-600" for="isdefault">تعيين كعنوان افتراضي</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end border-top pt-4">
                                    <a href="{{ route('user.addresses') }}" class="btn btn-light rounded-pill px-4 py-2 me-2">إلغاء</a>
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-save me-1"></i> حفظ العنوان
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .rounded-4 { border-radius: 1rem !important; }
    .rounded-start-4 { border-top-right-radius: 1rem !important; border-bottom-right-radius: 1rem !important; }
    .rounded-end-4 { border-top-left-radius: 1rem !important; border-bottom-left-radius: 1rem !important; }
    
    .fw-600 { font-weight: 600; }
    
    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.25rem rgba(240, 193, 75, 0.25);
        border: 1px solid #f0c14b !important;
    }
    
    .input-group-text { border-right: none !important; }
    
    .page-title {
        font-weight: 800;
        position: relative;
        padding-bottom: 10px;
    }
    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 40px;
        height: 4px;
        background: #f0c14b;
        border-radius: 2px;
    }
    
    .custom-switch .form-check-input { width: 3rem; height: 1.5rem; cursor: pointer; }
    .custom-radio .form-check-input { cursor: pointer; width: 1.2rem; height: 1.2rem; }
    .custom-radio .form-check-label { cursor: pointer; padding-right: 5px; }

    .breadcrumb-item + .breadcrumb-item::before {
        float: right;
        padding-left: 0.5rem;
    }
</style>
@endsection
