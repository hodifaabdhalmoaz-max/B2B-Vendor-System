<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Address;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected $cartService;

    protected $orderService;

    public function __construct(CartService $cartService, OrderService $orderService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        $items = $this->cartService->getCartContents();
        $cartProductIds = $items->pluck('id')->map(fn ($id) => (int) $id)->all();

        $defaultAddress = Auth::check()
            ? Address::forUser(Auth::id())->default()->first()
            : null;

        $recommendedProducts = Product::active()
            ->select([
                'id',
                'name',
                'slug',
                'short_description',
                'regular_price',
                'sale_price',
                'image',
                'images',
                'category_id',
                'brand_id',
                'featured',
                'is_offer',
                'quantity',
                'created_at',
            ])
            ->whereNotIn('id', $cartProductIds)
            ->withCount(['reviews as active_reviews_count' => fn ($query) => $query->where('status', true)])
            ->withAvg(['reviews as active_reviews_avg' => fn ($query) => $query->where('status', true)], 'rating')
            ->orderByDesc('featured')
            ->orderByDesc('is_offer')
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        return view('cart', compact('defaultAddress', 'items', 'recommendedProducts'));
    }

    public function add_to_cart(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'color_id' => 'nullable|integer|exists:colors,id',
            'size_id' => 'nullable|integer|exists:sizes,id',
        ]);

        $product = Product::with([
            'colors' => fn ($query) => $query->active()->ordered(),
            'sizes' => fn ($query) => $query->active()->ordered(),
        ])->findOrFail($request->id);

        if ($product->stock_status !== 'instock' || $product->quantity < $request->quantity) {
            return redirect()->back()->with('error', __('messages.requested_quantity_unavailable'));
        }

        $selectedOptions = $this->resolveSelectedProductOptions($product, $request);

        if ($selectedOptions['error']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $selectedOptions['error']);
        }

        $price = $this->resolveProductPrice($product, $selectedOptions['price_adjustment']);

        $this->cartService->addToCart([
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => (int) $request->quantity,
            'price' => $price,
            'options' => $selectedOptions['options'],
        ]);

        return redirect()->back();
    }

    public function increase_cart_quantity($rowId)
    {
        $this->cartService->increaseQuantity($rowId);

        return redirect()->back();
    }

    public function decrease_cart_quantity($rowId)
    {
        $this->cartService->decreaseQuantity($rowId);

        return redirect()->back();
    }

    public function remove_item($rowId)
    {
        $this->cartService->removeItem($rowId);

        return redirect()->back();
    }

    public function empty_item()
    {
        $this->cartService->emptyCart();

        return redirect()->back();
    }

    public function apply_coupon_code(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:100',
        ]);

        $result = $this->cartService->applyCoupon($request->coupon_code);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function remove_coupon_code()
    {
        $result = $this->cartService->removeCoupon();

        return redirect()->back()->with('success', $result['message']);
    }

    public function checkout()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->cartService->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('messages.cart_empty'));
        }

        $address = Address::where('user_id', Auth::id())->where('isdefault', 1)->first();

        return view('checkout', compact('address'));
    }

    public function place_an_order(PlaceOrderRequest $request)
    {
        try {
            $orderData = $request->getOrderData();
            $paymentMode = $orderData['mode'];
            unset($orderData['mode']);

            $order = $this->orderService->createOrder($orderData, $paymentMode);
            $this->orderService->storeOrderInSession($order);

            return redirect()->route('cart.order.confirmation')->with('success', __('messages.order_created_successfully'));
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('messages.order_creation_failed'));
        }
    }

    public function order_confirmation()
    {
        $order = $this->orderService->getOrderFromSession();

        if ($order) {
            return view('order-confirmation', compact('order'));
        }

        return redirect()->route('cart.index');
    }

    private function resolveSelectedProductOptions(Product $product, Request $request): array
    {
        $options = [];
        $priceAdjustment = 0.0;

        if ($product->colors->isNotEmpty()) {
            if (! $request->filled('color_id')) {
                return [
                    'error' => 'يرجى اختيار لون المنتج قبل إضافته إلى السلة.',
                    'options' => [],
                    'price_adjustment' => 0.0,
                ];
            }

            $color = $product->colors->firstWhere('id', (int) $request->color_id);

            if (! $color) {
                return [
                    'error' => 'اللون المحدد غير متاح لهذا المنتج.',
                    'options' => [],
                    'price_adjustment' => 0.0,
                ];
            }

            $priceAdjustment += (float) ($color->pivot->price_adjustment ?? 0);
            $options['color_id'] = $color->id;
            $options['color_name'] = $color->name;
            $options['color_code'] = $color->code;
            $options['color_hex'] = $color->hex_code;
            $options['color_price_adjustment'] = $this->formatMoney((float) ($color->pivot->price_adjustment ?? 0));
        }

        if ($product->sizes->isNotEmpty()) {
            if (! $request->filled('size_id')) {
                return [
                    'error' => 'يرجى اختيار مقاس المنتج قبل إضافته إلى السلة.',
                    'options' => [],
                    'price_adjustment' => 0.0,
                ];
            }

            $size = $product->sizes->firstWhere('id', (int) $request->size_id);

            if (! $size) {
                return [
                    'error' => 'المقاس المحدد غير متاح لهذا المنتج.',
                    'options' => [],
                    'price_adjustment' => 0.0,
                ];
            }

            $priceAdjustment += (float) ($size->pivot->price_adjustment ?? 0);
            $options['size_id'] = $size->id;
            $options['size_name'] = $size->name;
            $options['size_code'] = $size->code;
            $options['size_price_adjustment'] = $this->formatMoney((float) ($size->pivot->price_adjustment ?? 0));
        }

        return [
            'error' => null,
            'options' => $options,
            'price_adjustment' => $priceAdjustment,
        ];
    }

    private function resolveProductPrice(Product $product, float $priceAdjustment = 0.0): string
    {
        $regularPrice = (float) $product->regular_price;
        $salePrice = (float) $product->sale_price;
        $price = (($salePrice > 0 && $salePrice < $regularPrice) ? $salePrice : $regularPrice) + $priceAdjustment;

        return $this->formatMoney(max(0, $price));
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
