@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>{{ __('Edit reseller') }}</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">{{ __('Dashboard') }}</div></a></li>
                <li><i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i></li>
                <li><a href="{{ route('admin.resellers.index') }}"><div class="text-tiny">{{ __('Resellers') }}</div></a></li>
                <li><i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i></li>
                <li><div class="text-tiny">{{ $reseller->username }}</div></li>
            </ul>
        </div>

        @if(session('status'))
            <p class="alert alert-success">{{ session('status') }}</p>
        @endif
        @error('reseller')
            <p class="alert alert-danger">{{ $message }}</p>
        @enderror

        <div class="wg-box mb-27">
            <form class="form-new-product form-style-1" action="{{ route('admin.resellers.update', $reseller) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.resellers._form', ['reseller' => $reseller])

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">{{ __('Save changes') }}</button>
                </div>
            </form>
        </div>

        <div class="wg-box">
            <h5>{{ __('Security actions') }}</h5>
            <form class="form-new-product form-style-1" action="{{ route('admin.resellers.password', $reseller) }}" method="POST">
                @csrf
                <fieldset class="name">
                    <div class="body-title">{{ __('New temporary password') }}</div>
                    <input class="flex-grow" type="password" name="password" autocomplete="new-password">
                </fieldset>
                @error('password') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
                <fieldset class="name">
                    <div class="body-title">{{ __('Confirm password') }}</div>
                    <input class="flex-grow" type="password" name="password_confirmation" autocomplete="new-password">
                </fieldset>
                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">{{ __('Reset password') }}</button>
                </div>
            </form>

            <div class="divider"></div>

            <div class="d-flex gap10 flex-wrap">
                @if($reseller->is_active)
                    <form method="POST" action="{{ route('admin.resellers.suspend', $reseller) }}">
                        @csrf
                        <button class="tf-button style-1 w208" type="submit">{{ __('Suspend reseller') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.resellers.reactivate', $reseller) }}">
                        @csrf
                        <button class="tf-button style-1 w208" type="submit">{{ __('Reactivate reseller') }}</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.resellers.destroy', $reseller) }}">
                    @csrf
                    @method('DELETE')
                    <button class="tf-button style-1 w208" type="submit">{{ __('Delete if safe') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
