<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartService
{
    /**
     * Add product to cart
     */
    public function addToCart(array $productData): void
    {
        Cart::instance('cart')->add(
            $productData['id'],
            $productData['name'],
            $productData['quantity'],
            $productData['price'],
            $productData['options'] ?? []
        )->associate('App\Models\Product');
    }

    /**
     * Update cart item quantity
     */
    public function updateCartQuantity(string $rowId, int $quantity): void
    {
        Cart::instance('cart')->update($rowId, $quantity);
    }

    /**
     * Increase cart item quantity by 1
     */
    public function increaseQuantity(string $rowId): void
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty + 1;
        $this->updateCartQuantity($rowId, $qty);
    }

    /**
     * Decrease cart item quantity by 1
     */
    public function decreaseQuantity(string $rowId): void
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = max(1, $product->qty - 1); // Ensure quantity doesn't go below 1
        $this->updateCartQuantity($rowId, $qty);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(string $rowId): void
    {
        Cart::instance('cart')->remove($rowId);
    }

    /**
     * Empty the entire cart
     */
    public function emptyCart(): void
    {
        Cart::instance('cart')->destroy();
    }

    /**
     * Get cart contents
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCartContents()
    {
        return Cart::instance('cart')->content();
    }

    /**
     * Get cart count
     */
    public function getCartCount(): int
    {
        return Cart::instance('cart')->count();
    }

    /**
     * Get cart subtotal
     */
    public function getCartSubtotal(): string
    {
        return Cart::instance('cart')->subtotal();
    }

    /**
     * Get cart tax
     */
    public function getCartTax(): string
    {
        return Cart::instance('cart')->tax();
    }

    /**
     * Get cart total
     */
    public function getCartTotal(): string
    {
        return Cart::instance('cart')->total();
    }

    /**
     * Apply coupon code to cart
     */
    public function applyCoupon(string $couponCode): array
    {
        if (empty($couponCode)) {
            return [
                'success' => false,
                'message' => 'كود الخصم غير صحيح!',
            ];
        }

        $coupon = $this->validateCoupon($couponCode);

        if (! $coupon) {
            return [
                'success' => false,
                'message' => 'كود الخصم غير صحيح أو منتهي الصلاحية!',
            ];
        }

        // Store coupon in session
        Session::put('coupon', [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'cart_value' => $coupon->cart_value,
        ]);

        // Calculate discount
        $this->calculateDiscount();

        return [
            'success' => true,
            'message' => 'تم تطبيق كود الخصم بنجاح!',
        ];
    }

    /**
     * Validate coupon code
     */
    protected function validateCoupon(string $couponCode): ?Coupon
    {
        return Coupon::where('code', $couponCode)
            ->where('is_active', true)
            ->where('expiry_date', '>=', Carbon::today())
            ->where('cart_value', '<=', $this->normalizeMoney($this->getCartSubtotal()))
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->first();
    }

    /**
     * Calculate discount based on applied coupon
     */
    public function calculateDiscount(): void
    {
        $discount = 0;

        if (Session::has('coupon')) {
            $coupon = Session::get('coupon');

            if ($coupon['type'] === 'fixed') {
                $discount = $coupon['value'];
            } else {
                $discount = ($this->normalizeMoney($this->getCartSubtotal()) * $coupon['value']) / 100;
            }

            $discount = min($discount, $this->normalizeMoney($this->getCartSubtotal()));
            $subtotalAfterDiscount = $this->normalizeMoney($this->getCartSubtotal()) - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            Session::put('discounts', [
                'discount' => $this->formatSessionAmount($discount),
                'subtotal' => $this->formatSessionAmount($subtotalAfterDiscount),
                'tax' => $this->formatSessionAmount($taxAfterDiscount),
                'total' => $this->formatSessionAmount($totalAfterDiscount),
            ]);
        }
    }

    private function normalizeMoney(string|int|float $value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }

    private function formatSessionAmount(string|int|float $value): string
    {
        $formatted = number_format($this->normalizeMoney($value), 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(): array
    {
        Session::forget(['coupon', 'discounts']);

        return [
            'success' => true,
            'message' => 'تم إزالة كود الخصم بنجاح!',
        ];
    }

    /**
     * Check if coupon is applied
     */
    public function hasCoupon(): bool
    {
        return Session::has('coupon');
    }

    /**
     * Get applied coupon details
     */
    public function getAppliedCoupon(): ?array
    {
        return Session::get('coupon');
    }

    /**
     * Get discount details
     */
    public function getDiscountDetails(): ?array
    {
        return Session::get('discounts');
    }

    /**
     * Set checkout amounts in session
     */
    public function setCheckoutAmounts(): void
    {
        if ($this->getCartCount() <= 0) {
            Session::forget('checkout');

            return;
        }

        if ($this->hasCoupon()) {
            $discounts = $this->getDiscountDetails();
            Session::put('checkout', [
                'discount' => $discounts['discount'],
                'subtotal' => $discounts['subtotal'],
                'tax' => $discounts['tax'],
                'total' => $discounts['total'],
            ]);
        } else {
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => $this->getCartSubtotal(),
                'tax' => $this->getCartTax(),
                'total' => $this->getCartTotal(),
            ]);
        }
    }

    /**
     * Get checkout amounts
     */
    public function getCheckoutAmounts(): ?array
    {
        return Session::get('checkout');
    }

    /**
     * Clear cart and related sessions after order
     */
    public function clearCartAfterOrder(): void
    {
        $this->emptyCart();
        Session::forget(['checkout', 'coupon', 'discounts']);
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->getCartCount() === 0;
    }

    /**
     * Get cart summary for display
     */
    public function getCartSummary(): array
    {
        $summary = [
            'items' => $this->getCartContents(),
            'count' => $this->getCartCount(),
            'subtotal' => $this->getCartSubtotal(),
            'tax' => $this->getCartTax(),
            'total' => $this->getCartTotal(),
            'has_coupon' => $this->hasCoupon(),
        ];

        if ($this->hasCoupon()) {
            $summary['coupon'] = $this->getAppliedCoupon();
            $summary['discounts'] = $this->getDiscountDetails();
        }

        return $summary;
    }
}
