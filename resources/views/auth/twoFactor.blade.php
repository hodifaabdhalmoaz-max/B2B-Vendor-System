@extends('layouts.base')

@section('title', 'التحقق بخطوتين')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">التحقق بخطوتين</h4>
                </div>
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success" role="alert">
                            {{ session('message') }}
                        </div>
                    @endif
                    
                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-4x text-primary mb-3"></i>
                        <p>لقد أرسلنا رمز التحقق إلى بريدك الإلكتروني. يرجى إدخال الرمز أدناه للمتابعة.</p>
                    </div>

                    <form method="POST" action="{{ route('two-factor.verify') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="code" class="col-md-4 col-form-label text-md-right">رمز التحقق</label>

                            <div class="col-md-6">
                                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" required autocomplete="off" autofocus>

                                @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    تحقق
                                </button>

                                <a href="{{ route('two-factor.resend') }}" class="btn btn-link">
                                    إعادة إرسال الرمز
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
