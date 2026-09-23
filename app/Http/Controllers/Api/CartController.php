<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Get cart contents.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $cart = [
            'items' => Cart::instance('cart')->content(),
            'count' => Cart::instance('cart')->count(),
            'subtotal' => Cart::instance('cart')->subtotal(),
            'tax' => Cart::instance('cart')->tax(),
            'total' => Cart::instance('cart')->total(),
        ];

        // Get applied coupon if any
        if (Session::has('coupon')) {
            $cart['coupon'] = Session::get('coupon');
        }

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }

    /**
     * Add item to cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::find($request->id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Check if product is in stock
        if ($product->stock_status !== 'instock') {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock',
            ], 400);
        }

        // Check if quantity is available
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Requested quantity is not available',
            ], 400);
        }

        $price = $this->resolveProductPrice($product);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $request->quantity,
            'price' => $price,
            'weight' => 0,
            'options' => $request->options ?? [],
        ])->associate(Product::class);

        // Log cart action
        $this->auditService->log('cart_add', [
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'price' => $price,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $cartItem,
                'cart' => [
                    'count' => Cart::instance('cart')->count(),
                    'subtotal' => Cart::instance('cart')->subtotal(),
                    'total' => Cart::instance('cart')->total(),
                ],
            ],
            'message' => 'Product added to cart',
        ]);
    }

    /**
     * Update cart item.
     *
     * @param  string  $rowId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $rowId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cartItem = Cart::instance('cart')->get($rowId);

        if (! $cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        // Check if product is still available
        $product = Product::find($cartItem->id);

        if (! $product || $product->stock_status !== 'instock' || $product->quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Requested quantity is not available',
            ], 400);
        }

        // Update cart item
        Cart::instance('cart')->update($rowId, $request->quantity);

        // Log cart action
        $this->auditService->log('cart_update', [
            'product_id' => $cartItem->id,
            'old_quantity' => $cartItem->qty,
            'new_quantity' => $request->quantity,
            'price' => $cartItem->price,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'item' => Cart::instance('cart')->get($rowId),
                'cart' => [
                    'count' => Cart::instance('cart')->count(),
                    'subtotal' => Cart::instance('cart')->subtotal(),
                    'total' => Cart::instance('cart')->total(),
                ],
            ],
            'message' => 'Cart updated',
        ]);
    }

    /**
     * Remove item from cart.
     *
     * @param  string  $rowId
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);

        if (! $cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        // Log cart action
        $this->auditService->log('cart_remove', [
            'product_id' => $cartItem->id,
            'quantity' => $cartItem->qty,
            'price' => $cartItem->price,
        ]);

        // Remove from cart
        Cart::instance('cart')->remove($rowId);

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => [
                    'count' => Cart::instance('cart')->count(),
                    'subtotal' => Cart::instance('cart')->subtotal(),
                    'total' => Cart::instance('cart')->total(),
                ],
            ],
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Clear cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        // Log cart action
        $cartItems = [];
        foreach (Cart::instance('cart')->content() as $item) {
            $cartItems[] = [
                'product_id' => $item->id,
                'quantity' => $item->qty,
                'price' => $item->price,
            ];
        }

        $this->auditService->log('cart_empty', [
            'items' => $cartItems,
            'total' => Cart::instance('cart')->total(),
        ]);

        // Clear cart
        Cart::instance('cart')->destroy();
        Session::forget('coupon');

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }

    /**
     * Apply coupon.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->where('expiry_date', '>=', now())
            ->first();

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon',
            ], 400);
        }

        // Validate usage limit if set
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon usage limit has been reached',
            ], 400);
        }

        // Calculate discount and check minimum cart value
        $subtotal = $this->normalizeMoney(Cart::instance('cart')->subtotal());
        if ($subtotal < $coupon->cart_value) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum purchase amount of '.$coupon->cart_value.' is required for this coupon',
            ], 400);
        }

        $discount = 0;
        if ($coupon->type === 'fixed') {
            $discount = min($coupon->value, $subtotal);
        } else {
            $discount = ($subtotal * $coupon->value) / 100;
        }

        // Store coupon in session
        Session::put('coupon', [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'cart_value' => $coupon->cart_value,
            'discount' => $discount,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'coupon' => Session::get('coupon'),
                'cart' => [
                    'subtotal' => Cart::instance('cart')->subtotal(),
                    'discount' => $discount,
                    'total' => number_format($this->normalizeMoney(Cart::instance('cart')->total()) - $discount, 2, '.', ''),
                ],
            ],
            'message' => 'Coupon applied successfully',
        ]);
    }

    private function resolveProductPrice(Product $product): string
    {
        $regularPrice = (float) $product->regular_price;
        $salePrice = (float) $product->sale_price;
        $price = ($salePrice > 0 && $salePrice < $regularPrice) ? $salePrice : $regularPrice;

        return number_format($price, 2, '.', '');
    }

    private function normalizeMoney(string|int|float $value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }
}
