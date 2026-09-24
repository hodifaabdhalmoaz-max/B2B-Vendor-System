@extends('reseller.layout')
@section('title', __('Wishlist'))
@section('content')
<h1 class="h3 mb-4">{{ __('Wishlist') }}</h1>
<div class="row g-3">@forelse($products as $product)<div class="col-12 col-sm-6 col-lg-4">@include('reseller.partials.product-card')</div>@empty<p>{{ __('No products found.') }}</p>@endforelse</div><div class="mt-4">{{ $products->links() }}</div>
@endsection
