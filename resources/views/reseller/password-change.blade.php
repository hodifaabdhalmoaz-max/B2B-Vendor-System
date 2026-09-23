@extends('layouts.app')

@section('content')
<main class="pt-90">
    <section class="login-register container">
        <div class="login-form">
            <h2 class="mb-4">{{ __('Change password') }}</h2>
            <form method="POST" action="{{ route('reseller.password.update') }}">
                @csrf
                <div class="form-floating mb-3">
                    <input id="password" type="password" class="form-control form-control_gray @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    <label for="password">{{ __('New password') }}</label>
                    @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input id="password_confirmation" type="password" class="form-control form-control_gray" name="password_confirmation" required autocomplete="new-password">
                    <label for="password_confirmation">{{ __('Confirm password') }}</label>
                </div>

                <button class="btn btn-primary w-100" type="submit">{{ __('Update password') }}</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="btn btn-link w-100" type="submit">{{ __('Logout') }}</button>
            </form>
        </div>
    </section>
</main>
@endsection
