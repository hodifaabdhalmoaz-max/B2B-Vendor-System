@extends('reseller.layout')
@section('title', __('Catalog'))
@section('content')
<h1 class="h3">{{ __('Catalog') }}</h1>
<form class="portal-card row g-3 my-3" action="{{ route('reseller.catalog') }}" method="GET">
    <div class="col-12 col-md-5"><label for="search">{{ __('Search catalog') }}</label><input id="search" class="form-control" name="q" maxlength="200" value="{{ $filters['q'] ?? '' }}"></div>
    <div class="col-12 col-md-4"><label for="category">{{ __('Category') }}</label><select id="category" class="form-select" name="category"><option value="">{{ __('All categories') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    <div class="col-12 d-flex flex-wrap gap-3 align-items-center">
        <label><input type="checkbox" name="in_stock" value="1" @checked($filters['in_stock'] ?? false)> {{ __('In stock only') }}</label><label><input type="checkbox" name="offers" value="1" @checked($filters['offers'] ?? false)> {{ __('Offers') }}</label>
        <button class="btn btn-primary">{{ __('Search') }}</button><a class="btn btn-outline-secondary" href="{{ route('reseller.catalog') }}">{{ __('Reset filters') }}</a>
    </div>
</form>
<div class="row g-3">@forelse($products as $product)<div class="col-12 col-sm-6 col-lg-4">@include('reseller.partials.product-card')</div>@empty<p>{{ __('No products found.') }}</p>@endforelse</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
