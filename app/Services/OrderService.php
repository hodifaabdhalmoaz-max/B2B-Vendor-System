<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderService
{
    private const STATUS_ORDERED = 'ordered';

    private const STATUS_PROCESSING = 'processing';

    protected $orderRepository;

    protected $cartService;

    public function __construct(OrderRepositoryInterface $orderRepository, CartService $cartService)
    {
        $this->orderRepository = $orderRepository;
        $this->cartService = $cartService;
    }

    /**
     * @throws Exception
     */
    public function createOrder(array $orderData, string $paymentMode): Order
    {
        if ($this->cartService->isEmpty()) {
            throw new Exception('Cart is empty.');
        }

        return DB::transaction(function () use ($orderData, $paymentMode) {
            $pricedItems = $this->validateAndPriceCartItems();
            $checkoutAmounts = $this->calculateCheckoutAmounts($pricedItems);
            $address = $this->handleOrderAddress($orderData);
            $order = $this->createOrderRecord($address, $checkoutAmounts);

            $this->createOrderItems($order, $pricedItems);
            $this->decrementProductStock($pricedItems);
            $this->applyCouponUsage();
            $this->createTransaction($order, $paymentMode);
            $this->cartService->clearCartAfterOrder();

            return $order;
        });
    }

    protected function handleOrderAddress(array $orderData): Address
    {
        $userId = Auth::id();
        $address = Address::where('user_id', $userId)->where('isdefault', true)->first();

        if (! $address) {
            $address = new Address;
            $address->fill([
                'name' => $orderData['name'],
                'phone' => $orderData['phone'],
                'zip' => $orderData['zip'],
                'state' => $orderData['state'],
                'city' => $orderData['city'],
                'address' => $orderData['address'],
                'locality' => $orderData['locality'],
                'landmark' => $orderData['landmark'],
                'country' => 'Yemen',
                'user_id' => $userId,
                'isdefault' => true,
            ]);
            $address->save();
        }

        return $address;
    }

    protected function createOrderRecord(Address $address, array $checkoutAmounts): Order
    {
        $order = new Order;
        $order->fill([
            'user_id' => Auth::id(),
            'subtotal' => $checkoutAmounts['subtotal'],
            'discount' => $checkoutAmounts['discount'],
            'tax' => $checkoutAmounts['tax'],
            'total' => $checkoutAmounts['total'],
            'name' => $address->name,
            'phone' => $address->phone,
            'locality' => $address->locality,
            'address' => $address->address,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'landmark' => $address->landmark,
            'zip' => $address->zip,
            'type' => $address->type ?? 'home',
            'status' => self::STATUS_ORDERED,
            'is_shipping_different' => false,
            'delivered_date' => null,
            'canceled_date' => null,
        ]);
        $order->save();

        return $order;
    }

    protected function createOrderItems(Order $order, array $pricedItems): void
    {
        foreach ($pricedItems as $item) {
            $orderItem = new OrderItem;
            $orderItem->fill([
                'product_id' => $item['product']->id,
                'order_id' => $order->id,
                'price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'options' => $item['options'],
                'rstatus' => false,
            ]);
            $orderItem->save();
        }
    }

    protected function createTransaction(Order $order, string $paymentMode): Transaction
    {
        $transaction = new Transaction;
        $transaction->fill([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'mode' => $paymentMode,
            'status' => $this->getTransactionStatus($paymentMode),
        ]);
        $transaction->save();

        return $transaction;
    }

    protected function validateAndPriceCartItems(): array
    {
        $cartItems = $this->cartService->getCartContents();
        $requestedQuantities = [];

        foreach ($cartItems as $item) {
            $productId = (int) $item->id;
            $requestedQuantities[$productId] = ($requestedQuantities[$productId] ?? 0) + (int) $item->qty;
        }

        $products = Product::whereIn('id', array_keys($requestedQuantities))
            ->with([
                'colors' => fn ($query) => $query->active()->ordered(),
                'sizes' => fn ($query) => $query->active()->ordered(),
            ])
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($requestedQuantities as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product || $product->stock_status !== 'instock') {
                throw new Exception('One or more cart products are no longer available.');
            }

            if ((int) $product->quantity < $quantity) {
                throw new Exception("Requested quantity is not available for {$product->name}.");
            }
        }

        $pricedItems = [];
        foreach ($cartItems as $item) {
            $product = $products->get((int) $item->id);
            $options = $item->options ?? [];
            $options = is_object($options) && method_exists($options, 'toArray') ? $options->toArray() : (array) $options;
            $validatedOptions = $this->validateCartItemOptions($product, $options);

            $pricedItems[] = [
                'product' => $product,
                'quantity' => (int) $item->qty,
                'unit_price' => $this->resolveProductPrice($product, $validatedOptions['price_adjustment']),
                'options' => $validatedOptions['options'],
            ];
        }

        return $pricedItems;
    }

    protected function calculateCheckoutAmounts(array $pricedItems): array
    {
        $subtotal = array_reduce(
            $pricedItems,
            fn (float $carry, array $item): float => $carry + ((float) $item['unit_price'] * $item['quantity']),
            0.0
        );

        $discount = $this->calculateCouponDiscount($subtotal);
        $subtotalAfterDiscount = max(0, $subtotal - $discount);
        $tax = ($subtotalAfterDiscount * (float) config('cart.tax', 0)) / 100;
        $total = $subtotalAfterDiscount + $tax;

        return [
            'subtotal' => $this->formatMoney($subtotal),
            'discount' => $this->formatMoney($discount),
            'tax' => $this->formatMoney($tax),
            'total' => $this->formatMoney($total),
        ];
    }

    protected function calculateCouponDiscount(float $subtotal): float
    {
        $couponData = Session::get('coupon');
        if (! $couponData || empty($couponData['code'])) {
            return 0.0;
        }

        $coupon = Coupon::where('code', $couponData['code'])
            ->where('is_active', true)
            ->where('expiry_date', '>=', now()->toDateString())
            ->lockForUpdate()
            ->first();

        if (! $coupon) {
            throw new Exception('Coupon is invalid or expired.');
        }

        if ((float) $coupon->cart_value > $subtotal) {
            throw new Exception('Cart value is below the coupon minimum.');
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new Exception('Coupon usage limit has been reached.');
        }

        if ($coupon->type === 'fixed') {
            return min((float) $coupon->value, $subtotal);
        }

        return min(($subtotal * (float) $coupon->value) / 100, $subtotal);
    }

    protected function decrementProductStock(array $pricedItems): void
    {
        $quantitiesByProduct = [];

        foreach ($pricedItems as $item) {
            $productId = $item['product']->id;
            $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + $item['quantity'];
        }

        $products = collect($pricedItems)->pluck('product', 'product.id');

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $product = $products->get($productId);
            $product->quantity = max(0, (int) $product->quantity - $quantity);
            if ($product->quantity === 0) {
                $product->stock_status = 'outofstock';
            }
            $product->save();
        }
    }

    protected function applyCouponUsage(): void
    {
        $couponData = Session::get('coupon');
        if (! $couponData || empty($couponData['code'])) {
            return;
        }

        Coupon::where('code', $couponData['code'])->increment('used_count');
    }

    protected function validateCartItemOptions(Product $product, array $options): array
    {
        $validatedOptions = [];
        $priceAdjustment = 0.0;

        if ($product->colors->isNotEmpty()) {
            $colorId = (int) ($options['color_id'] ?? 0);
            $color = $product->colors->firstWhere('id', $colorId);

            if (! $color) {
                throw new Exception("Selected color is no longer available for {$product->name}.");
            }

            $priceAdjustment += (float) ($color->pivot->price_adjustment ?? 0);
            $validatedOptions['color_id'] = $color->id;
            $validatedOptions['color_name'] = $color->name;
            $validatedOptions['color_code'] = $color->code;
            $validatedOptions['color_hex'] = $color->hex_code;
            $validatedOptions['color_price_adjustment'] = $this->formatMoney((float) ($color->pivot->price_adjustment ?? 0));
        }

        if ($product->sizes->isNotEmpty()) {
            $sizeId = (int) ($options['size_id'] ?? 0);
            $size = $product->sizes->firstWhere('id', $sizeId);

            if (! $size) {
                throw new Exception("Selected size is no longer available for {$product->name}.");
            }

            $priceAdjustment += (float) ($size->pivot->price_adjustment ?? 0);
            $validatedOptions['size_id'] = $size->id;
            $validatedOptions['size_name'] = $size->name;
            $validatedOptions['size_code'] = $size->code;
            $validatedOptions['size_price_adjustment'] = $this->formatMoney((float) ($size->pivot->price_adjustment ?? 0));
        }

        return [
            'options' => $validatedOptions,
            'price_adjustment' => $priceAdjustment,
        ];
    }

    protected function resolveProductPrice(Product $product, float $priceAdjustment = 0.0): string
    {
        $regularPrice = (float) $product->regular_price;
        $salePrice = (float) $product->sale_price;
        $price = (($salePrice > 0 && $salePrice < $regularPrice) ? $salePrice : $regularPrice) + $priceAdjustment;

        return $this->formatMoney(max(0, $price));
    }

    protected function formatMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    protected function getTransactionStatus(string $paymentMode): string
    {
        return match ($paymentMode) {
            'card', 'paypal' => self::STATUS_PROCESSING,
            default => 'pending',
        };
    }

    public function updateOrderStatus(Order $order, string $status): bool
    {
        return $this->orderRepository->updateStatus($order->id, $status);
    }

    public function cancelOrder(Order $order, ?string $reason = null): bool
    {
        if (! $this->canCancelOrder($order)) {
            return false;
        }

        return $this->orderRepository->markAsCancelled($order->id, $reason);
    }

    public function canCancelOrder(Order $order): bool
    {
        return in_array($order->status, [self::STATUS_ORDERED, self::STATUS_PROCESSING], true);
    }

    public function getOrderWithItems(int $orderId): ?Order
    {
        $order = $this->orderRepository->find($orderId);

        return $order?->loadMissing(['orderItems.product', 'transaction']);
    }

    public function getUserOrders(User $user, int $perPage = 10)
    {
        return $this->orderRepository->getByUser($user->id, $perPage);
    }

    public function getOrderStatistics(): array
    {
        return $this->orderRepository->getStatistics();
    }

    public function getRecentOrders(int $limit = 10)
    {
        return $this->orderRepository->getRecent($limit);
    }

    public function processPayment(Order $order, string $paymentMode, array $paymentData = []): bool
    {
        return match ($paymentMode) {
            'card' => $this->processCardPayment($order, $paymentData),
            'paypal' => $this->processPaypalPayment($order, $paymentData),
            'cod' => $this->processCodPayment($order),
            default => false,
        };
    }

    protected function processCardPayment(Order $order, array $paymentData): bool
    {
        return true;
    }

    protected function processPaypalPayment(Order $order, array $paymentData): bool
    {
        return true;
    }

    protected function processCodPayment(Order $order): bool
    {
        $transaction = $order->transaction;
        if ($transaction) {
            $transaction->status = 'pending';

            return $transaction->save();
        }

        return true;
    }

    public function storeOrderInSession(Order $order): void
    {
        Session::put('order_id', $order->id);
    }

    public function getOrderFromSession(): ?Order
    {
        $orderId = Session::get('order_id');

        if (! $orderId) {
            return null;
        }

        $order = $this->orderRepository->find((int) $orderId);

        return $order?->loadMissing(['orderItems.product', 'transaction']);
    }

    public function clearOrderFromSession(): void
    {
        Session::forget('order_id');
    }
}
