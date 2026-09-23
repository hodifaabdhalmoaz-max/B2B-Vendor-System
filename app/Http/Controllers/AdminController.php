<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductColorImage;
use App\Models\Size;
use App\Models\Slide;
use App\Services\RevenueAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    protected RevenueAnalyticsService $revenueService;

    protected \App\Services\ImageService $imageService;

    public function __construct(RevenueAnalyticsService $revenueService, \App\Services\ImageService $imageService)
    {
        $this->revenueService = $revenueService;
        $this->imageService = $imageService;
    }

    private function allowedStorefrontCategoryContexts(): array
    {
        return array_keys(config('storefront.category_contexts', []));
    }

    private function allowedStorefrontProductSections(): array
    {
        return array_keys(config('storefront.product_sections', []));
    }

    private function normalizeStorefrontSelection(?array $values, array $allowed): array
    {
        return array_values(array_intersect($values ?? [], $allowed));
    }

    private function defaultCategoryContextsForCreate(array $contexts): array
    {
        return array_values(array_unique(array_merge(['home.all'], $contexts)));
    }

    private function productCategorySyncIds(Request $request): array
    {
        return collect($request->input('category_ids', []))
            ->push($request->input('category_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeSlug(?string $slug, string $fallback): string
    {
        $value = trim($slug ?: $fallback);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s_-]+/u', '', $value) ?? '';
        $value = preg_replace('/[\s_]+/u', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : (string) Str::uuid();
    }

    private function uniqueSlugForModel(string $modelClass, ?string $slug, string $fallback, ?int $ignoreId = null): string
    {
        $baseSlug = $this->normalizeSlug($slug, $fallback);
        $uniqueSlug = $baseSlug;
        $counter = 2;

        while (
            $modelClass::where('slug', $uniqueSlug)
                ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
                ->exists()
        ) {
            $uniqueSlug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $uniqueSlug;
    }

    private function uniqueCategorySlug(?string $slug, string $fallback, ?int $ignoreId = null): string
    {
        return $this->uniqueSlugForModel(Category::class, $slug, $fallback, $ignoreId);
    }

    private function uniqueBrandSlug(?string $slug, string $fallback, ?int $ignoreId = null): string
    {
        return $this->uniqueSlugForModel(Brand::class, $slug, $fallback, $ignoreId);
    }

    private function uniqueProductSlug(?string $slug, string $fallback, ?int $ignoreId = null): string
    {
        return $this->uniqueSlugForModel(Product::class, $slug, $fallback, $ignoreId);
    }

    private function normalizeSku(?string $sku, string $fallback): string
    {
        $value = trim($sku ?: $fallback);
        $value = mb_strtoupper($value, 'UTF-8');
        $value = preg_replace('/[^\p{Arabic}\p{L}\p{N}]+/u', '-', $value) ?? '';
        $value = trim($value, '-');
        $value = mb_substr($value, 0, 64, 'UTF-8');

        return $value !== '' ? $value : 'SKU-'.Str::upper(Str::random(8));
    }

    private function uniqueProductSku(?string $sku, string $fallback, ?int $ignoreId = null): string
    {
        $baseSku = $this->normalizeSku($sku, $fallback);
        $uniqueSku = $baseSku;
        $counter = 2;

        while (
            Product::where('SKU', $uniqueSku)
                ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
                ->exists()
        ) {
            $suffix = '-'.$counter;
            $uniqueSku = mb_substr($baseSku, 0, 255 - mb_strlen($suffix, 'UTF-8'), 'UTF-8').$suffix;
            $counter++;
        }

        return $uniqueSku;
    }

    private function clearHomeCaches(): void
    {
        foreach ([
            'home_categories_market_v1',
            'home_categories_market_v2',
            'home_brands',
            'home_featured_products',
            'home_latest_products',
            'home_offer_products',
            'home_market_products_v2',
            'home_banner_products',
            'shop_all_categories_market_v2',
            'categories_recommended_products_v1',
            'categories_recommended_products_v2_all',
            'categories_recommended_products_v2_kids',
            'categories_recommended_products_v2_gifts',
            'categories_recommended_products_v2_toys',
            'categories_recommended_products_v2_mother',
            'categories_recommended_products_v2_care',
            'categories_recommended_products_v2_accessories',
            'categories_recommended_products_v2_shoes',
            'categories_recommended_products_v3_all_all',
            'categories_recommended_products_v3_kids_all',
            'categories_recommended_products_v3_gifts_all',
            'categories_recommended_products_v3_toys_all',
            'categories_recommended_products_v3_mother_all',
            'categories_recommended_products_v3_care_all',
            'categories_recommended_products_v3_accessories_all',
            'categories_recommended_products_v3_shoes_all',
        ] as $key) {
            Cache::forget($key);
        }

        Cache::forever('storefront_cache_version', ((int) Cache::get('storefront_cache_version', 1)) + 1);
    }

    public function index(Request $request)
    {
        // تحديد الفترة الزمنية للفرز مع التحقق من صحة البيانات
        $allowedPeriods = ['this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'last_year'];
        $period = $request->get('period', 'this_week');

        // التحقق من أن الفترة المطلوبة مسموحة
        if (! in_array($period, $allowedPeriods)) {
            $period = 'this_week';
        }

        $dateRange = $this->getDateRange($period);

        // إحصائيات الطلبات والمبالغ — استعلام تجميعي واحد بدلاً من 8 استعلامات منفصلة
        $orderStats = Order::selectRaw("
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'ordered' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
            SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as cancelled_orders,
            COALESCE(SUM(total), 0) as total_amount,
            COALESCE(SUM(CASE WHEN status = 'ordered' THEN total ELSE 0 END), 0) as pending_amount,
            COALESCE(SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END), 0) as delivered_amount,
            COALESCE(SUM(CASE WHEN status = 'canceled' THEN total ELSE 0 END), 0) as cancelled_amount
        ")->first();

        $totalOrders = (int) $orderStats->total_orders;
        $pendingOrders = (int) $orderStats->pending_orders;
        $deliveredOrders = (int) $orderStats->delivered_orders;
        $cancelledOrders = (int) $orderStats->cancelled_orders;
        $totalAmount = (float) $orderStats->total_amount;
        $pendingAmount = (float) $orderStats->pending_amount;
        $deliveredAmount = (float) $orderStats->delivered_amount;
        $cancelledAmount = (float) $orderStats->cancelled_amount;

        // استخدام الخدمة المحقونة للحصول على بيانات الإيرادات
        $analytics = $this->revenueService->getRevenueAnalytics($period);

        // إعداد البيانات للعرض
        $revenueData = [
            'current_revenue' => $analytics['current']['revenue'] > 0 ? $analytics['current']['revenue'] : $totalAmount,
            'current_orders' => $analytics['current']['total_orders'] > 0 ? $analytics['current']['total_orders'] : $totalAmount,
            'revenue_change' => $analytics['changes']['revenue'],
            'orders_change' => $analytics['changes']['orders'],
            'total_orders_count' => $totalOrders,
            'delivered_orders_count' => $deliveredOrders,
            'total_amount' => $totalAmount,
        ];

        $chartData = $analytics['chart_data'];

        // الطلبات الحديثة مع بيانات المستخدم
        $recentOrders = Order::with(['user', 'orderItems'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.index', compact(
            'totalOrders',
            'pendingOrders',
            'deliveredOrders',
            'cancelledOrders',
            'totalAmount',
            'pendingAmount',
            'deliveredAmount',
            'cancelledAmount',
            'recentOrders',
            'revenueData',
            'chartData',
            'period'
        ));
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    //Brand
    public function brands()
    {
        $brands = Brand::withCount('products')->orderBy('id', 'DESC')->paginate(10);

        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->merge([
            'slug' => $this->uniqueBrandSlug($request->input('slug'), $request->input('name', '')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $brand = new Brand;
        $brand->name = $request->name;
        $brand->slug = $request->slug;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_name = Str::uuid().'.webp';
            $this->imageService->generateBrandThumbnail($image, $file_name);
            $brand->image = $file_name;
        }
        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has added succesfully!');
    }

    public function brand_edit($id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request)
    {
        $request->merge([
            'slug' => $this->uniqueBrandSlug($request->input('slug'), $request->input('name', ''), (int) $request->id),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $brand = Brand::findOrFail($request->id);
        $brand->name = $request->name;
        $brand->slug = $request->slug;
        if ($request->hasFile('image')) {
            if ($brand->image) {
                $this->imageService->deleteBrandImage($brand->image);
            }
            $image = $request->file('image');
            $file_name = Str::uuid().'.webp';
            $this->imageService->generateBrandThumbnail($image, $file_name);
            $brand->image = $file_name;
        }
        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has updated succesfully!');
    }

    public function brand_delete($id)
    {
        $brand = Brand::findOrFail($id);
        if ($brand->image) {
            $this->imageService->deleteBrandImage($brand->image);
        }
        $brand->delete();

        return redirect()->route('admin.brands')->with('status', 'Brand has deleted succesfully!');
    }
    //End Brand

    //Category
    public function categories()
    {
        $categories = Category::withCount('products')->orderBy('id', 'DESC')->paginate(10);

        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        $parentCategories = Category::select('id', 'name')->orderBy('name')->get();

        return view('admin.category-add', compact('parentCategories'));
    }

    public function category_store(Request $request)
    {
        $request->merge([
            'slug' => $this->uniqueCategorySlug($request->input('slug'), $request->input('name', '')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'storefront_contexts' => 'nullable|array',
            'storefront_contexts.*' => 'string|in:'.implode(',', $this->allowedStorefrontCategoryContexts()),
            'storefront_order' => 'nullable|integer|min:0|max:65535',
        ]);

        $storefrontContexts = $this->defaultCategoryContextsForCreate(
            $request->input('storefront_contexts', [])
        );

        $category = new Category;
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->parent_id = $request->input('parent_id') ?: null;
        $category->storefront_contexts = $this->normalizeStorefrontSelection(
            $storefrontContexts,
            $this->allowedStorefrontCategoryContexts()
        );
        $category->storefront_order = (int) $request->input('storefront_order', 0);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_name = Str::uuid().'.webp';
            $this->imageService->generateCategoryThumbnail($image, $file_name);
            $category->image = $file_name;
        }
        $category->save();
        $this->clearHomeCaches();

        return redirect()->route('admin.categories')->with('status', 'Category has added succesfully!');
    }

    public function category_edit($id)
    {
        $category = Category::findOrFail($id);
        $parentCategories = Category::select('id', 'name')
            ->where('id', '<>', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.category-edit', compact('category', 'parentCategories'));
    }

    public function category_update(Request $request)
    {
        $request->merge([
            'slug' => $this->uniqueCategorySlug($request->input('slug'), $request->input('name', ''), (int) $request->id),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([(int) $request->id])],
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'storefront_contexts' => 'nullable|array',
            'storefront_contexts.*' => 'string|in:'.implode(',', $this->allowedStorefrontCategoryContexts()),
            'storefront_order' => 'nullable|integer|min:0|max:65535',
        ]);

        $category = Category::findOrFail($request->id);
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->parent_id = $request->input('parent_id') ?: null;
        $category->storefront_contexts = $this->normalizeStorefrontSelection(
            $request->input('storefront_contexts', []),
            $this->allowedStorefrontCategoryContexts()
        );
        $category->storefront_order = (int) $request->input('storefront_order', 0);
        if ($request->hasFile('image')) {
            if ($category->image) {
                $this->imageService->deleteCategoryImage($category->image);
            }
            $image = $request->file('image');
            $file_name = Str::uuid().'.webp';
            $this->imageService->generateCategoryThumbnail($image, $file_name);
            $category->image = $file_name;
        }
        $category->save();
        $this->clearHomeCaches();

        return redirect()->route('admin.categories')->with('status', 'Category has updated succesfully!');
    }

    public function category_delete($id)
    {
        $category = Category::findOrFail($id);
        if ($category->image) {
            $this->imageService->deleteCategoryImage($category->image);
        }
        $category->delete();
        $this->clearHomeCaches();

        return redirect()->route('admin.categories')->with('status', 'Category has deleted succesfully!');
    }
    //End Category

    //Product
    public function products()
    {
        $products = Product::with(['category', 'brand'])->orderBy('created_at', 'DESC')->paginate(10);

        return view('admin.products', compact('products'));
    }

    public function product_add()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $colors = Color::active()->ordered()->get();
        $sizes = Size::active()->ordered()->get();

        return view('admin.product-add', compact('categories', 'brands', 'colors', 'sizes'));
    }

    public function product_store(Request $request)
    {
        $request->merge([
            'slug' => $this->uniqueProductSlug($request->input('slug'), $request->input('name', '')),
            'SKU' => $this->uniqueProductSku($request->input('SKU'), $request->input('name', '')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'SKU' => 'required|string|max:255',
            'stock_status' => 'required|in:instock,outofstock',
            'featured' => 'required|boolean',
            'is_offer' => 'required|boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'required|mimes:png,jpg,jpeg,webp|max:2048',
            'category_id' => 'required|integer|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'brand_id' => 'required|integer|exists:brands,id',
            'colors' => 'nullable|array',
            'colors.*' => 'integer|exists:colors,id',
            'color_images' => 'nullable|array',
            'color_images.*' => 'nullable|array',
            'color_images.*.*' => 'image|mimes:png,jpg,jpeg,webp|max:2048',
            'sizes' => 'nullable|array',
            'sizes.*' => 'integer|exists:sizes,id',
            'dimensions' => 'nullable|string',
            'weight' => 'nullable|string',
            'storefront_sections' => 'nullable|array',
            'storefront_sections.*' => 'string|in:'.implode(',', $this->allowedStorefrontProductSections()),
            'storefront_order' => 'nullable|integer|min:0|max:65535',
        ], [
            'category_id.required' => 'يجب اختيار فئة للمنتج',
            'category_id.integer' => 'يجب اختيار فئة صحيحة',
            'category_id.exists' => 'الفئة المختارة غير موجودة',
            'brand_id.required' => 'يجب اختيار علامة تجارية للمنتج',
            'brand_id.integer' => 'يجب اختيار علامة تجارية صحيحة',
            'brand_id.exists' => 'العلامة التجارية المختارة غير موجودة',
            'name.required' => 'اسم المنتج مطلوب',
            'SKU.unique' => 'رمز المنتج موجود مسبقاً',
            'regular_price.numeric' => 'السعر يجب أن يكون رقماً',
            'sale_price.numeric' => 'سعر التخفيض يجب أن يكون رقماً',
            'quantity.integer' => 'الكمية يجب أن تكون رقماً صحيحاً',
        ]);

        $colorIds = $this->requestedProductColorIds($request);

        $product = new Product;
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->is_offer = $request->is_offer;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->dimensions = $request->dimensions;
        $product->weight = $request->weight;
        $product->storefront_sections = $this->normalizeStorefrontSelection(
            $request->input('storefront_sections', []),
            $this->allowedStorefrontProductSections()
        );
        $product->storefront_order = (int) $request->input('storefront_order', 0);

        $current_timestamp = (string) Str::uuid();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $current_timestamp.'.webp';
            $this->imageService->generateProductImages($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = [];
        $gallery_images = '';
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedfileExtion = ['jpg', 'png', 'jpeg', 'webp'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array(strtolower($gextension), $allowedfileExtion);
                if ($gcheck) {
                    $gfileName = $current_timestamp.'-'.$counter.'.webp';
                    $this->imageService->generateProductImages($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }

        $product->images = $gallery_images;
        $product->save();
        $product->categories()->sync($this->productCategorySyncIds($request));

        if ($colorIds !== []) {
            $product->colors()->sync($colorIds);
            $colorNames = Color::whereIn('id', $colorIds)->pluck('name')->toArray();
            $product->color = implode(', ', $colorNames);
        } else {
            $product->color = null;
        }
        $this->pruneDetachedProductColorImages($product, $colorIds);
        $this->storeProductColorImages($request, $product);

        if ($request->has('sizes')) {
            $product->sizes()->sync($request->sizes);
            $sizeNames = Size::whereIn('id', $request->sizes)->pluck('name')->toArray();
            $product->size = implode(', ', $sizeNames);
        } else {
            $product->size = null;
        }
        $product->save();

        // Clear filter caching and homepage caching
        Cache::forget('search_filters');
        $this->clearHomeCaches();

        return redirect()->route('admin.products')->with('status', 'تم إضافة المنتج بنجاح!');
    }

    public function product_edit($id)
    {
        $product = Product::with(['categories', 'colors', 'sizes', 'colorImages'])->findOrFail($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $colors = Color::active()->ordered()->get();
        $sizes = Size::active()->ordered()->get();

        return view('admin.product-edit', compact('product', 'categories', 'brands', 'colors', 'sizes'));
    }

    public function product_update(Request $request)
    {
        $request->merge([
            'slug' => $this->uniqueProductSlug($request->input('slug'), $request->input('name', ''), (int) $request->id),
            'SKU' => $this->uniqueProductSku($request->input('SKU'), $request->input('name', ''), (int) $request->id),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'SKU' => 'required|string|max:255',
            'stock_status' => 'required|in:instock,outofstock',
            'featured' => 'required|boolean',
            'is_offer' => 'required|boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|mimes:png,jpg,jpeg,webp|max:2048',
            'category_id' => 'required|integer|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'brand_id' => 'required|integer|exists:brands,id',
            'colors' => 'nullable|array',
            'colors.*' => 'integer|exists:colors,id',
            'color_images' => 'nullable|array',
            'color_images.*' => 'nullable|array',
            'color_images.*.*' => 'image|mimes:png,jpg,jpeg,webp|max:2048',
            'remove_color_images' => 'nullable|array',
            'remove_color_images.*' => 'integer|exists:product_color_images,id',
            'sizes' => 'nullable|array',
            'sizes.*' => 'integer|exists:sizes,id',
            'dimensions' => 'nullable|string',
            'weight' => 'nullable|string',
            'storefront_sections' => 'nullable|array',
            'storefront_sections.*' => 'string|in:'.implode(',', $this->allowedStorefrontProductSections()),
            'storefront_order' => 'nullable|integer|min:0|max:65535',
        ], [
            'category_id.required' => 'يجب اختيار فئة للمنتج',
            'category_id.integer' => 'يجب اختيار فئة صحيحة',
            'category_id.exists' => 'الفئة المختارة غير موجودة',
            'brand_id.required' => 'يجب اختيار علامة تجارية للمنتج',
            'brand_id.integer' => 'يجب اختيار علامة تجارية صحيحة',
            'brand_id.exists' => 'العلامة التجارية المختارة غير موجودة',
            'name.required' => 'اسم المنتج مطلوب',
            'SKU.unique' => 'رمز المنتج موجود مسبقاً',
            'regular_price.numeric' => 'السعر يجب أن يكون رقماً',
            'sale_price.numeric' => 'سعر التخفيض يجب أن يكون رقماً',
            'quantity.integer' => 'الكمية يجب أن تكون رقماً صحيحاً',
        ]);

        $colorIds = $this->requestedProductColorIds($request);

        $product = Product::findOrFail($request->id);
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->is_offer = $request->is_offer;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->dimensions = $request->dimensions;
        $product->weight = $request->weight;
        $product->storefront_sections = $this->normalizeStorefrontSelection(
            $request->input('storefront_sections', []),
            $this->allowedStorefrontProductSections()
        );
        $product->storefront_order = (int) $request->input('storefront_order', 0);

        $current_timestamp = (string) Str::uuid();

        if ($request->hasFile('image')) {
            if ($product->image) {
                $this->imageService->deleteProductImage($product->image);
            }
            $image = $request->file('image');
            $imageName = $current_timestamp.'.webp';
            $this->imageService->generateProductImages($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = [];
        $gallery_images = '';
        $counter = 1;

        if ($request->hasFile('images')) {
            if ($product->images) {
                $this->imageService->deleteProductGallery($product->images);
            }

            $allowedfileExtion = ['jpg', 'png', 'jpeg', 'webp'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array(strtolower($gextension), $allowedfileExtion);
                if ($gcheck) {
                    $gfileName = $current_timestamp.'-'.$counter.'.webp';
                    $this->imageService->generateProductImages($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
            $product->images = $gallery_images;
        }

        $product->save();
        $product->categories()->sync($this->productCategorySyncIds($request));

        if ($colorIds !== []) {
            $product->colors()->sync($colorIds);
            $colorNames = Color::whereIn('id', $colorIds)->pluck('name')->toArray();
            $product->color = implode(', ', $colorNames);
        } else {
            $product->colors()->detach();
            $product->color = null;
        }
        $this->deleteRemovedProductColorImages($request, $product);
        $this->pruneDetachedProductColorImages($product, $colorIds);
        $this->storeProductColorImages($request, $product);

        if ($request->has('sizes')) {
            $product->sizes()->sync($request->sizes);
            $sizeNames = Size::whereIn('id', $request->sizes)->pluck('name')->toArray();
            $product->size = implode(', ', $sizeNames);
        } else {
            $product->sizes()->detach();
            $product->size = null;
        }
        $product->save();

        // Clear filter caching and homepage caching
        Cache::forget('search_filters');
        $this->clearHomeCaches();

        return redirect()->route('admin.products')->with('status', 'تم تحديث المنتج بنجاح!');
    }

    public function product_delete($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image) {
            $this->imageService->deleteProductImage($product->image);
        }

        if ($product->images) {
            $this->imageService->deleteProductGallery($product->images);
        }

        $product->colorImages()->get()->each(function (ProductColorImage $image): void {
            $this->imageService->deleteProductColorImage($image->image_path);
        });

        $product->delete();
        $this->clearHomeCaches();

        return redirect()->route('admin.products')->with('status', 'product has deleted succesfully!');
    }

    private function storeProductColorImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('color_images')) {
            return;
        }

        $attachedColorIds = $product->colors()->pluck('colors.id')->map(fn ($id) => (int) $id)->all();

        foreach ($request->file('color_images', []) as $colorId => $files) {
            $colorId = (int) $colorId;

            if (! in_array($colorId, $attachedColorIds, true)) {
                continue;
            }

            foreach ((array) $files as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $sortOrder = ((int) ProductColorImage::where('product_id', $product->id)
                    ->where('color_id', $colorId)
                    ->max('sort_order')) + 1;

                ProductColorImage::create([
                    'product_id' => $product->id,
                    'color_id' => $colorId,
                    'image_path' => $this->imageService->generateProductColorImage($file, Str::uuid().'.webp'),
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    private function requestedProductColorIds(Request $request): array
    {
        $selectedColorIds = collect($request->input('colors', []));
        $uploadedColorIds = collect(array_keys($request->file('color_images', [])));

        $requestedColorIds = $selectedColorIds
            ->merge($uploadedColorIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($requestedColorIds->isEmpty()) {
            return [];
        }

        return Color::whereIn('id', $requestedColorIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function deleteRemovedProductColorImages(Request $request, Product $product): void
    {
        $imageIds = collect($request->input('remove_color_images', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($imageIds->isEmpty()) {
            return;
        }

        ProductColorImage::where('product_id', $product->id)
            ->whereIn('id', $imageIds)
            ->get()
            ->each(function (ProductColorImage $image): void {
                $this->imageService->deleteProductColorImage($image->image_path);
                $image->delete();
            });
    }

    private function pruneDetachedProductColorImages(Product $product, array $attachedColorIds): void
    {
        $attachedColorIds = collect($attachedColorIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $query = ProductColorImage::where('product_id', $product->id);

        if ($attachedColorIds->isNotEmpty()) {
            $query->whereNotIn('color_id', $attachedColorIds);
        }

        $query->get()->each(function (ProductColorImage $image): void {
            $this->imageService->deleteProductColorImage($image->image_path);
            $image->delete();
        });
    }

    //End Product
    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(12);

        return view('admin.coupons', compact('coupons'));
    }

    public function coupon_add()
    {

        return view('admin.coupon-add');
    }

    public function coupon_store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',

        ]);
        $coupon = new Coupon;
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'coupon has been added succesfully!');

    }

    public function coupon_edit($id)
    {
        $coupon = Coupon::findOrFail($id);

        return view('admin.coupon-edit', compact('coupon'));
    }

    public function coupon_update(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);
        $coupon = Coupon::findOrFail($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'coupon has been updated succesfully!');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons')->with('status', 'coupon has been deleted succesfully!');
    }

    /**
     * تحديد النطاق الزمني بناءً على الفترة المحددة
     */
    private function getDateRange($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'this_week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'last_week':
                return [
                    'start' => $now->copy()->subWeek()->startOfWeek(),
                    'end' => $now->copy()->subWeek()->endOfWeek(),
                ];
            case 'this_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth(),
                ];
            case 'this_year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                ];
            case 'last_year':
                return [
                    'start' => $now->copy()->subYear()->startOfYear(),
                    'end' => $now->copy()->subYear()->endOfYear(),
                ];
            default:
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
        }
    }

    // Color Management
    public function colors()
    {
        $colors = Color::withCount('products')->orderBy('id', 'DESC')->paginate(10);

        return view('admin.colors', compact('colors'));
    }

    public function add_color()
    {
        return view('admin.color-add');
    }

    public function color_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:colors,code',
            'hex_code' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $color = new Color;
        $color->name = $request->name;
        $color->code = $request->code;
        $color->hex_code = $request->hex_code;
        $color->description = $request->description;
        $color->order = $request->order ?? 0;
        $color->is_active = $request->is_active;
        $color->save();

        Cache::forget('search_filters');

        return redirect()->route('admin.colors')->with('status', 'تم إضافة اللون بنجاح!');
    }

    public function color_edit($id)
    {
        $color = Color::findOrFail($id);

        return view('admin.color-edit', compact('color'));
    }

    public function color_update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:colors,code,'.$request->id,
            'hex_code' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $color = Color::findOrFail($request->id);
        $color->name = $request->name;
        $color->code = $request->code;
        $color->hex_code = $request->hex_code;
        $color->description = $request->description;
        $color->order = $request->order ?? 0;
        $color->is_active = $request->is_active;
        $color->save();

        Cache::forget('search_filters');

        return redirect()->route('admin.colors')->with('status', 'تم تحديث اللون بنجاح!');
    }

    public function color_delete($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        Cache::forget('search_filters');

        return redirect()->route('admin.colors')->with('status', 'تم حذف اللون بنجاح!');
    }

    // Size Management
    public function sizes()
    {
        $sizes = Size::withCount('products')->orderBy('id', 'DESC')->paginate(10);

        return view('admin.sizes', compact('sizes'));
    }

    public function add_size()
    {
        return view('admin.size-add');
    }

    public function size_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:sizes,code',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $size = new Size;
        $size->name = $request->name;
        $size->code = $request->code;
        $size->description = $request->description;
        $size->order = $request->order ?? 0;
        $size->is_active = $request->is_active;
        $size->save();

        Cache::forget('search_filters');

        return redirect()->route('admin.sizes')->with('status', 'تم إضافة المقاس بنجاح!');
    }

    public function size_edit($id)
    {
        $size = Size::findOrFail($id);

        return view('admin.size-edit', compact('size'));
    }

    public function size_update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:sizes,code,'.$request->id,
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $size = Size::findOrFail($request->id);
        $size->name = $request->name;
        $size->code = $request->code;
        $size->description = $request->description;
        $size->order = $request->order ?? 0;
        $size->is_active = $request->is_active;
        $size->save();

        Cache::forget('search_filters');

        return redirect()->route('admin.sizes')->with('status', 'تم تحديث المقاس بنجاح!');
    }

    public function size_delete($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        Cache::forget('search_filters');

        return redirect()->route('admin.sizes')->with('status', 'تم حذف المقاس بنجاح!');
    }

    // Slideshow Management (الشرائح المتحركة)
    public function slides()
    {
        $slides = Slide::ordered()->paginate(10);

        return view('admin.slides', compact('slides'));
    }

    public function add_slide()
    {
        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'image' => 'required|mimes:png,jpg,jpeg,webp|max:2048',
            'status' => 'required|boolean',
            'order' => 'nullable|integer',
        ]);

        $slide = new Slide;
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;
        $slide->order = $request->order ?? 0;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_name = Str::uuid().'.'.$image->getClientOriginalExtension();

            $this->GenerateSlideImage($image, $file_name);
            $slide->image = $file_name;
        }

        $slide->save();

        Cache::forget('home_slides');

        return redirect()->route('admin.slides')->with('status', 'تم إضافة الشريحة بنجاح!');
    }

    public function slide_edit($id)
    {
        $slide = Slide::findOrFail($id);

        return view('admin.slide-edit', compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:slides,id',
            'tagline' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'image' => 'nullable|mimes:png,jpg,jpeg,webp|max:2048',
            'status' => 'required|boolean',
            'order' => 'nullable|integer',
        ]);

        $slide = Slide::findOrFail($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;
        $slide->order = $request->order ?? 0;

        if ($request->hasFile('image')) {
            if ($slide->image && File::exists(public_path('uploads/slides').'/'.$slide->image)) {
                File::delete(public_path('uploads/slides').'/'.$slide->image);
            }

            $image = $request->file('image');
            $file_name = Str::uuid().'.'.$image->getClientOriginalExtension();

            $this->GenerateSlideImage($image, $file_name);
            $slide->image = $file_name;
        }

        $slide->save();

        Cache::forget('home_slides');

        return redirect()->route('admin.slides')->with('status', 'تم تحديث الشريحة بنجاح!');
    }

    public function slide_delete($id)
    {
        $slide = Slide::findOrFail($id);
        if ($slide->image && File::exists(public_path('uploads/slides').'/'.$slide->image)) {
            File::delete(public_path('uploads/slides').'/'.$slide->image);
        }
        $slide->delete();

        Cache::forget('home_slides');

        return redirect()->route('admin.slides')->with('status', 'تم حذف الشريحة بنجاح!');
    }

    private function GenerateSlideImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        if (! File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        $img = Image::read($image->path());
        $img->resize(600, 800, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upSize();
        })->save($destinationPath.'/'.$imageName);
    }
}
