<?php

use App\Http\Controllers\Admin\AdPlacementController;
use App\Http\Controllers\Admin\MarketingCampaignController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Marketing\CampaignAssetImageController;
use App\Http\Controllers\MessageCenterController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

// // Redirect / → /home (permanent 301)
// Route::permanentRedirect('/', '/home');
// // Language switching route
Route::get('/language/{locale}', [LanguageController::class, 'switchLang'])->name('language.switch');

// ═══════════════════════════════════════════════════════════
// Search routes — separate limiter to prevent search abuse (40/min)
// ═══════════════════════════════════════════════════════════
Route::middleware(['smart.throttle:search'])->group(function () {
    Route::get('/shop/search', [ShopController::class, 'search'])->name('shop.search');
    Route::get('/shop/quick-search', [ShopController::class, 'quickSearch'])->name('shop.quick.search');
});

// ═══════════════════════════════════════════════════════════
// Public routes — generous limit (120/min for guests, 240/min for auth)
// Uses named limiter 'web-public' from RateLimitServiceProvider
// ═══════════════════════════════════════════════════════════
Route::middleware(['smart.throttle:public'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/shop', fn () => redirect()->route('categories.index'))->name('shop.index');
    Route::get('/categories', [ShopController::class, 'categories'])->name('categories.index');
    Route::get('/shop/category/{slug}', [CategoryPageController::class, 'show'])->name('shop.category');
    Route::get('/shop/brand/{slug}', [ShopController::class, 'brand'])->name('shop.brand');
    Route::get('/offers', [ShopController::class, 'offers'])->name('shop.offers');
    Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');
    Route::get('/messages', [MessageCenterController::class, 'index'])->name('messages.index');
    Route::get('/messages/{section}', [MessageCenterController::class, 'section'])
        ->whereIn('section', ['orders', 'activity', 'promo', 'news'])
        ->name('messages.section');
    Route::post('/messages/clear', [MessageCenterController::class, 'clear'])->name('messages.clear');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/contact', [HomeController::class, 'contact_send'])->name('contact.send');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
    Route::get('/returns', [HomeController::class, 'returns'])->name('returns');
    Route::get('/shipping', [HomeController::class, 'shipping'])->name('shipping');
    Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
    Route::post('/newsletter/subscribe', [HomeController::class, 'newsletter_subscribe'])->name('newsletter.subscribe');
    Route::get('/campaign-assets/{asset}/image', [CampaignAssetImageController::class, 'show'])->name('campaign-assets.image');
});

// ═══════════════════════════════════════════════════════════
// Cart — moderate limit (60/min)
// ═══════════════════════════════════════════════════════════
Route::middleware(['smart.throttle:cart'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
    Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.qty.increase');
    Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.qty.decrease');
    Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
    Route::delete('/cart/clear', [CartController::class, 'empty_item'])->name('cart.empty');
    Route::post('/cart/apply-coupon', [CartController::class, 'apply_coupon_code'])->name('cart.coupon.apply');
    Route::delete('/cart/remove-coupon', [CartController::class, 'remove_coupon_code'])->name('cart.coupon.remove');
});

// ═══════════════════════════════════════════════════════════
// Checkout — auth required, moderate limit (30/min)
// Previous limit was 5/min which was way too aggressive
// ═══════════════════════════════════════════════════════════
Route::middleware(['auth', 'smart.throttle:checkout'])->group(function () {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/place-an-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order');
    Route::get('/order-confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');
});

// ═══════════════════════════════════════════════════════════
// Wishlist — moderate limit (40/min)
// ═══════════════════════════════════════════════════════════
Route::middleware(['smart.throttle:wishlist'])->group(function () {
    Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::delete('/wishlist/item/remove/{rowId}', [WishlistController::class, 'remove_item'])->name('wishlist.item.remove');
    Route::delete('/wishlist/item/remove-by-id/{id}', [WishlistController::class, 'remove_item_by_id'])->name('wishlist.item.remove.by.id');
    Route::delete('/wishlist/clear', [WishlistController::class, 'empty_wishlist'])->name('wishlist.items.clear');
    Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'move_to_cart'])->name('wishlist.move-to-cart');
});

// ═══════════════════════════════════════════════════════════
// User dashboard — auth required, generous limit (60/min)
// ═══════════════════════════════════════════════════════════
Route::middleware(['auth', 'smart.throttle:user_dashboard'])->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/user/profile', [UserController::class, 'profileUpdate'])->name('user.profile.update');
    Route::get('/user/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/user/order/{id}', [UserController::class, 'orderDetails'])->name('user.order.details');
    Route::get('/user/wishlist', [UserController::class, 'wishlist'])->name('user.wishlist');

    // Addresses
    Route::get('/user/addresses', [UserController::class, 'addresses'])->name('user.addresses');
    Route::get('/user/address/add', [UserController::class, 'addressAdd'])->name('user.address.add');
    Route::post('/user/address/store', [UserController::class, 'addressStore'])->name('user.address.store');
    Route::get('/user/address/edit/{id}', [UserController::class, 'addressEdit'])->name('user.address.edit');
    Route::post('/user/address/update/{id}', [UserController::class, 'addressUpdate'])->name('user.address.update');
    Route::delete('/user/address/delete/{id}', [UserController::class, 'addressDelete'])->name('user.address.delete');
    Route::post('/user/address/default/{id}', [UserController::class, 'addressSetDefault'])->name('user.address.default');

    // Reviews — تقييمات المنتجات
    Route::post('/review/{product_id}', [ReviewController::class, 'store'])->name('review.store');
    Route::post('/product/review/{product_id}', [ReviewController::class, 'store'])->name('product.review.store');
});

// ═══════════════════════════════════════════════════════════
// Admin — very generous limit (200/min base × 5 admin multiplier = 1000/min)
// Admins should never be rate-limited during normal operations
// ═══════════════════════════════════════════════════════════
Route::middleware(['auth', AuthAdmin::class, 'smart.throttle:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    //Brand
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('/admin/brand/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
    Route::get('/admin/brand/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
    Route::put('/admin/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
    Route::delete('/admin/brand/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');

    //Category
    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
    Route::post('/admin/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');
    Route::get('/admin/category/edit/{id}', [AdminController::class, 'category_edit'])->name('admin.category.edit');
    Route::put('/admin/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
    Route::delete('/admin/category/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');

    //Product
    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');
    Route::post('/admin/product/store', [AdminController::class, 'product_store'])->name('admin.product.store');
    Route::get('/admin/product/edit/{id}', [AdminController::class, 'product_edit'])->name('admin.product.edit');
    Route::put('/admin/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
    Route::delete('/admin/product/{id}/delete', [AdminController::class, 'product_delete'])->name('admin.product.delete');

    //Color
    Route::get('/admin/colors', [AdminController::class, 'colors'])->name('admin.colors');
    Route::get('/admin/color/add', [AdminController::class, 'add_color'])->name('admin.color.add');
    Route::post('/admin/color/store', [AdminController::class, 'color_store'])->name('admin.color.store');
    Route::get('/admin/color/edit/{id}', [AdminController::class, 'color_edit'])->name('admin.color.edit');
    Route::put('/admin/color/update', [AdminController::class, 'color_update'])->name('admin.color.update');
    Route::delete('/admin/color/{id}/delete', [AdminController::class, 'color_delete'])->name('admin.color.delete');

    //Size
    Route::get('/admin/sizes', [AdminController::class, 'sizes'])->name('admin.sizes');
    Route::get('/admin/size/add', [AdminController::class, 'add_size'])->name('admin.size.add');
    Route::post('/admin/size/store', [AdminController::class, 'size_store'])->name('admin.size.store');
    Route::get('/admin/size/edit/{id}', [AdminController::class, 'size_edit'])->name('admin.size.edit');
    Route::put('/admin/size/update', [AdminController::class, 'size_update'])->name('admin.size.update');
    Route::delete('/admin/size/{id}/delete', [AdminController::class, 'size_delete'])->name('admin.size.delete');

    //Slide
    Route::get('/admin/slides', [AdminController::class, 'slides'])->name('admin.slides');
    Route::get('/admin/slide/add', [AdminController::class, 'add_slide'])->name('admin.slide.add');
    Route::post('/admin/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
    Route::get('/admin/slide/edit/{id}', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');
    Route::put('/admin/slide/update', [AdminController::class, 'slide_update'])->name('admin.slide.update');
    Route::delete('/admin/slide/{id}/delete', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');

    // Marketing campaigns
    Route::prefix('/admin/marketing')->name('admin.marketing.')->group(function () {
        Route::resource('campaigns', MarketingCampaignController::class)->except(['show']);
        Route::resource('placements', AdPlacementController::class)->except(['show']);
    });

    //coupons
    Route::get('/admin/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
    Route::get('/admin/coupon/add', [AdminController::class, 'coupon_add'])->name('admin.coupon.add');
    Route::post('/admin/coupon/store', [AdminController::class, 'coupon_store'])->name('admin.coupon.store');
    Route::get('/admin/coupon/{id}/edit', [AdminController::class, 'coupon_edit'])->name('admin.coupon.edit');
    Route::put('/admin/coupon/update', [AdminController::class, 'coupon_update'])->name('admin.coupon.update');
    Route::delete('/admin/coupon/{id}/delete', [AdminController::class, 'coupon_delete'])->name('admin.coupon.delete');

    //Orders - إدارة الطلبات
    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
    Route::get('/admin/order/{order}', [AdminOrderController::class, 'show'])->name('admin.order.details');
    Route::get('/admin/order/{order}/edit', [AdminOrderController::class, 'edit'])->name('admin.order.edit');
    Route::put('/admin/order/{order}', [AdminOrderController::class, 'update'])->name('admin.order.update');
    Route::put('/admin/order/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.order.update.status');
    Route::get('/admin/order/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('admin.order.invoice');
    Route::get('/admin/order/{order}/tracking', [AdminOrderController::class, 'tracking'])->name('admin.order.tracking');
    Route::post('/admin/order/{order}/tracking', [AdminOrderController::class, 'addTracking'])->name('admin.order.tracking.add');
    Route::get('/admin/orders/statistics', [AdminOrderController::class, 'statistics'])->name('admin.orders.statistics');

    // Revenue Analytics - تحليل الإيرادات
    Route::get('/admin/revenue-analytics', [App\Http\Controllers\Admin\RevenueAnalyticsController::class, 'index'])->name('admin.revenue.analytics');
    Route::get('/admin/revenue-analytics/data', [App\Http\Controllers\Admin\RevenueAnalyticsController::class, 'getData'])->name('admin.revenue.data');
    Route::get('/admin/revenue-analytics/pdf', [App\Http\Controllers\Admin\RevenueAnalyticsController::class, 'downloadPDF'])->name('admin.revenue.pdf');
    Route::get('/admin/revenue-analytics/advanced', [App\Http\Controllers\Admin\RevenueAnalyticsController::class, 'advancedStats'])->name('admin.revenue.advanced');
});
