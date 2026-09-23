@extends('layouts.base')

@section('title', 'إعدادات التحقق بخطوتين')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            @include('user.partials.sidebar')
        </div>
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">إعدادات التحقق بخطوتين</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5>حالة التحقق بخطوتين</h5>
                        <p>
                            @if($user->two_factor_enabled)
                                <span class="badge bg-success">مفعل</span>
                                <p class="text-muted mt-2">التحقق بخطوتين مفعل لحسابك. سيتم طلب رمز تحقق عند تسجيل الدخول.</p>
                            @else
                                <span class="badge bg-danger">غير مفعل</span>
                                <p class="text-muted mt-2">التحقق بخطوتين غير مفعل لحسابك. نوصي بتفعيله لزيادة أمان حسابك.</p>
                            @endif
                        </p>
                    </div>

                    <div class="mb-4">
                        <h5>ما هو التحقق بخطوتين؟</h5>
                        <p>التحقق بخطوتين هو طبقة إضافية من الأمان لحسابك. عند تفعيله، ستحتاج إلى إدخال رمز تحقق يتم إرساله إلى بريدك الإلكتروني بالإضافة إلى كلمة المرور عند تسجيل الدخول.</p>
                    </div>

                    @if($user->two_factor_enabled)
                        <form method="POST" action="{{ route('user.two-factor.disable') }}">
                            @csrf
                            <div class="form-group">
                                <label for="password">كلمة المرور الحالية</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger mt-3">تعطيل التحقق بخطوتين</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('user.two-factor.enable') }}">
                            @csrf
                            <div class="form-group">
                                <label for="password">كلمة المرور الحالية</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">تفعيل التحقق بخطوتين</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
