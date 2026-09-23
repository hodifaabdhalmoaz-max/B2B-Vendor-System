@extends('layouts.app')

@section('content')
<main class="pt-90">
    <section class="container py-5">
        @if(session('status'))
            <p class="alert alert-success">{{ session('status') }}</p>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="p-4 border rounded bg-white">
                    <h2 class="mb-4">{{ __('Reseller account') }}</h2>
                    <p><strong>{{ __('Name') }}:</strong> {{ $user->name }}</p>
                    <p><strong>{{ __('Username') }}:</strong> {{ $user->username }}</p>
                    <p><strong>{{ __('Business name') }}:</strong> {{ $user->resellerProfile?->business_name ?: '-' }}</p>
                    <p><strong>{{ __('Account status') }}:</strong> {{ $user->is_active ? __('Active') : __('Inactive') }}</p>
                    <p><strong>{{ __('Business profile status') }}:</strong> {{ $user->resellerProfile?->status }}</p>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-primary" type="submit">{{ __('Logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
