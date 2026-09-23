@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>{{ __('Create reseller') }}</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">{{ __('Dashboard') }}</div></a></li>
                <li><i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i></li>
                <li><a href="{{ route('admin.resellers.index') }}"><div class="text-tiny">{{ __('Resellers') }}</div></a></li>
                <li><i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i></li>
                <li><div class="text-tiny">{{ __('Create') }}</div></li>
            </ul>
        </div>

        <div class="wg-box">
            <form class="form-new-product form-style-1" action="{{ route('admin.resellers.store') }}" method="POST">
                @csrf
                @include('admin.resellers._form', ['reseller' => new \App\Models\User()])

                <fieldset class="name">
                    <div class="body-title">{{ __('Temporary password') }} <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="password" name="password" required autocomplete="new-password">
                </fieldset>
                @error('password') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

                <fieldset class="name">
                    <div class="body-title">{{ __('Confirm temporary password') }} <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="password" name="password_confirmation" required autocomplete="new-password">
                </fieldset>

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
