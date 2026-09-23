@extends('layouts.app')

@section('content')
<main class="pt-90">
    
    @include('user.components.dashboard-header', ['title' => 'تفاصيل الحساب', 'description' => 'إدارة معلوماتك الشخصية وتحديث بيانات حسابك'])
    <section class="my-account container">

        <div class="row">
            <div class="col-lg-3 d-none d-md-block">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle me-3 fs-4"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">المعلومات الشخصية</h5>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row mb-4 align-items-center">
                                    <div class="col-md-3 text-center mb-3 mb-md-0">
                                        <div class="profile-photo-preview position-relative d-inline-block">
                                            @if($user->profile_photo)
                                            <img src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="rounded-circle border p-1 shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                            @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm text-white" style="width: 120px; height: 120px; font-size: 3.5rem; font-weight: 700; background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);">
                                                {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                                            </div>
                                            @endif
                                            <label for="profile_photo" class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow p-2 cursor-pointer border" title="تغيير الصورة">
                                                <i class="bi bi-camera text-primary"></i>
                                            </label>
                                            <input type="file" class="d-none" id="profile_photo" name="profile_photo" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <h6 class="fw-bold mb-1">صورة الملف الشخصي</h6>
                                        <p class="text-muted small mb-0">الحد الأقصى: 2MB | الأنواع المدعومة: JPEG, PNG, WEBP</p>
                                        @error('profile_photo')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="name" class="form-label fw-600">الاسم الكامل</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-person text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 rounded-end-4 py-2 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="أدخل اسمك الكامل" required>
                                        </div>
                                        @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label for="email" class="form-label fw-600">البريد الإلكتروني</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-envelope text-muted"></i></span>
                                            <input type="email" class="form-control bg-light border-0 rounded-end-4 py-2 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="example@domain.com" required>
                                        </div>
                                        @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="mobile" class="form-label fw-600">رقم الهاتف</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-telephone text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 rounded-end-4 py-2 @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="05xxxxxxxx">
                                        </div>
                                        @error('mobile')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-600">تاريخ الانضمام</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-calendar text-muted"></i></span>
                                            <input type="text" class="form-control bg-light border-0 rounded-end-4 py-2" value="{{ $user->created_at->format('d/m/Y') }}" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-save me-1"></i> حفظ التغييرات
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
    .rounded-4 {
        border-radius: 1rem !important;
    }

    .rounded-start-4 {
        border-top-right-radius: 1rem !important;
        border-bottom-right-radius: 1rem !important;
    }

    .rounded-end-4 {
        border-top-left-radius: 1rem !important;
        border-bottom-left-radius: 1rem !important;
    }

    .fw-600 {
        font-weight: 600;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .form-control:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.25rem rgba(240, 193, 75, 0.25);
        border: 1px solid #f0c14b !important;
    }

    .input-group-text {
        border-right: none !important;
    }

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

    .breadcrumb-item+.breadcrumb-item::before {
        float: right;
        padding-left: 0.5rem;
    }
</style>
@endsection
