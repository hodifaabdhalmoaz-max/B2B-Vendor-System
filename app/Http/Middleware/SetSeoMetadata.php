<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SetSeoMetadata
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $routeName = Route::currentRouteName();
        $locale = app()->getLocale();

        // Default SEO metadata
        $metadata = [
            'title' => config('app.name') . ' - متجر منتجات الأطفال',
            'metaDescription' => 'متجر متخصص في بيع منتجات الأطفال عالية الجودة بأسعار مناسبة. تسوق الآن واحصل على توصيل سريع لمنزلك.',
            'metaKeywords' => 'متجر أطفال, منتجات أطفال, ملابس أطفال, ألعاب أطفال, مستلزمات أطفال',
            'metaImage' => asset('assets/images/logo.png'),
            'canonical' => $request->url(),
            'robots' => 'index, follow',
            'ogTitle' => config('app.name') . ' - متجر منتجات الأطفال',
            'ogDescription' => 'متجر متخصص في بيع منتجات الأطفال عالية الجودة بأسعار مناسبة. تسوق الآن واحصل على توصيل سريع لمنزلك.',
            'ogImage' => asset('assets/images/logo.png'),
            'ogUrl' => $request->url(),
            'ogType' => 'website',
            'twitterCard' => 'summary_large_image',
            'twitterTitle' => config('app.name') . ' - متجر منتجات الأطفال',
            'twitterDescription' => 'متجر متخصص في بيع منتجات الأطفال عالية الجودة بأسعار مناسبة. تسوق الآن واحصل على توصيل سريع لمنزلك.',
            'twitterImage' => asset('assets/images/logo.png'),
        ];

        // Set metadata based on route
        if ($routeName) {
            switch ($routeName) {
                case 'home.index':
                    $metadata['title'] = $locale == 'ar' ? 'الصفحة الرئيسية - ' . config('app.name') : 'Home - ' . config('app.name');
                    break;

                case 'shop.index':
                    $metadata['title'] = 'المتجر - ' . config('app.name');
                    $metadata['metaDescription'] = 'تسوق أفضل منتجات الأطفال من متجرنا. مجموعة متنوعة من الملابس والألعاب والمستلزمات بأسعار مناسبة.';
                    $metadata['metaKeywords'] = 'متجر أطفال, منتجات أطفال, ملابس أطفال, ألعاب أطفال, مستلزمات أطفال, تسوق أونلاين';
                    $metadata['ogTitle'] = 'المتجر - ' . config('app.name');
                    $metadata['ogDescription'] = 'تسوق أفضل منتجات الأطفال من متجرنا. مجموعة متنوعة من الملابس والألعاب والمستلزمات بأسعار مناسبة.';
                    break;

                case 'cart.index':
                    $metadata['title'] = 'سلة التسوق - ' . config('app.name');
                    $metadata['metaDescription'] = 'سلة التسوق الخاصة بك في متجر ' . config('app.name') . ' للأطفال.';
                    $metadata['robots'] = 'noindex, nofollow'; // Don't index cart
                    break;

                case 'shop.product.details':
                    if ($request->route('slug')) {
                        $product = \App\Models\Product::where('slug', $request->route('slug'))->first();
                        if ($product) {
                            $metadata['title'] = $product->meta_title ?? $product->name . ' - ' . config('app.name');
                            $metadata['metaDescription'] = $product->meta_description ?? Str::limit(strip_tags($product->short_description), 160);
                            $metadata['metaKeywords'] = $product->meta_keywords ?? $product->name . ', ' . $product->category->name . ', منتجات أطفال';
                            $metadata['ogTitle'] = $product->meta_title ?? $product->name . ' - ' . config('app.name');
                            $metadata['ogDescription'] = $product->meta_description ?? Str::limit(strip_tags($product->short_description), 160);
                            $metadata['metaImage'] = asset('storage/' . $product->image);
                            $metadata['ogImage'] = asset('storage/' . $product->image);
                            $metadata['twitterImage'] = asset('storage/' . $product->image);
                            $metadata['ogType'] = 'product';
                        }
                    }
                    break;

                case 'checkout.index':
                    $metadata['title'] = 'إتمام الطلب - ' . config('app.name');
                    $metadata['metaDescription'] = 'إتمام طلبك في متجر ' . config('app.name') . ' للأطفال.';
                    $metadata['robots'] = 'noindex, nofollow'; // Don't index checkout
                    break;

                case 'login':
                    $metadata['title'] = 'تسجيل الدخول - ' . config('app.name');
                    $metadata['metaDescription'] = 'تسجيل الدخول إلى حسابك في متجر ' . config('app.name') . ' للأطفال.';
                    $metadata['robots'] = 'noindex, nofollow'; // Don't index login
                    break;

                case 'register':
                    $metadata['title'] = 'تسجيل حساب جديد - ' . config('app.name');
                    $metadata['metaDescription'] = 'إنشاء حساب جديد في متجر ' . config('app.name') . ' للأطفال.';
                    $metadata['robots'] = 'noindex, nofollow'; // Don't index register
                    break;

                // Add more routes as needed
            }
        }

        // Share metadata with all views
        View::share('seoMetadata', $metadata);

        return $next($request);
    }
}
