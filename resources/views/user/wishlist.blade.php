@extends('layouts.app')

@section('content')
<main class="pt-90">
    
    @include('user.components.dashboard-header', ['title' => 'المفضلة', 'description' => 'استعرض المنتجات التي نالت إعجابك واحتفظت بها لشرائها لاحقاً'])
    <section class="my-account container">

        <div class="row">
            <div class="col-lg-3 d-none d-md-block">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__wishlist">
                    @if($wishlistItems->count() > 0)
                        <div class="row g-4">
                            @foreach($wishlistItems as $item)
                                <div class="col-6 col-md-4 mb-4">
                                    <div class="product-card-wishlist bg-white rounded-4 shadow-sm h-100 overflow-hidden border transition">
                                        <div class="position-relative">
                                            @if($item->product->image)
                                                <img src="{{ asset('uploads/products/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="img-fluid w-100" style="height: 220px; object-fit: cover;">
                                            @else
                                                <img src="{{ asset('assets/images/placeholder.png') }}" alt="{{ $item->product->name }}" class="img-fluid w-100" style="height: 220px; object-fit: cover;">
                                            @endif
                                            
                                            <form action="{{ route('wishlist.item.remove.by.id', $item->product->id) }}" method="POST" class="position-absolute top-0 end-0 p-2">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-white btn-icon rounded-circle shadow-sm hover-danger" title="إزالة من المفضلة">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="p-3">
                                            <p class="text-muted small mb-1">{{ optional($item->product->category)->name }}</p>
                                            <h6 class="fw-bold mb-2 text-truncate">
                                                <a href="{{ route('shop.product.details', ['product_slug' => $item->product->slug]) }}" class="text-decoration-none text-dark">
                                                    {{ $item->product->name }}
                                                </a>
                                            </h6>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="price-box">
                                                    @if($item->product->sale_price)
                                                        <span class="fw-bold text-primary fs-5">{{ format_price($item->product->sale_price) }}</span>
                                                        <span class="text-muted small text-decoration-line-through ms-2">{{ format_price($item->product->regular_price) }}</span>
                                                    @else
                                                        <span class="fw-bold text-primary fs-5">{{ format_price($item->product->regular_price) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <form action="{{ route('cart.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $item->product->id }}">
                                                <input type="hidden" name="name" value="{{ $item->product->name }}">
                                                <input type="hidden" name="price" value="{{ $item->product->sale_price ?? $item->product->regular_price }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 shadow-sm d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-cart-plus me-2"></i> إضافة للسلة
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded-4 shadow-sm border-dashed">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                                <i class="bi bi-heart display-4 text-muted"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">قائمة المفضلة فارغة</h4>
                            <p class="text-muted mb-4 px-3">لم تضف أي منتجات إلى قائمتك المفضلة حتى الآن. استكشف متجرنا وأضف ما يعجبك!</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">تصفح المنتجات</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .product-card-wishlist {
        transition: all 0.3s ease;
    }
    .product-card-wishlist:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .btn-white {
        background-color: #fff;
        border: none;
    }
    .hover-danger:hover {
        background-color: #dc2626 !important;
    }
    .hover-danger:hover i {
        color: #fff !important;
    }
    .btn-icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .border-dashed {
        border: 2px dashed #e2e8f0 !important;
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
</style>
@endsection
