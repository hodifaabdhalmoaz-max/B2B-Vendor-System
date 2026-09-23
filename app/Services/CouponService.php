<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CouponService
{
    /**
     * Get all coupons with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllCoupons(int $perPage = 12): LengthAwarePaginator
    {
        return Coupon::orderBy('expiry_date', 'DESC')->paginate($perPage);
    }

    /**
     * Create a new coupon
     *
     * @param array $couponData
     * @return Coupon
     */
    public function createCoupon(array $couponData): Coupon
    {
        $coupon = new Coupon();
        $coupon->fill([
            'code' => strtoupper($couponData['code']),
            'type' => $couponData['type'],
            'value' => $couponData['value'],
            'cart_value' => $couponData['cart_value'],
            'expiry_date' => $couponData['expiry_date'],
            'usage_limit' => $couponData['usage_limit'] ?? null,
            'used_count' => 0,
            'is_active' => $couponData['is_active'] ?? true,
        ]);
        $coupon->save();

        return $coupon;
    }

    /**
     * Update existing coupon
     *
     * @param Coupon $coupon
     * @param array $couponData
     * @return bool
     */
    public function updateCoupon(Coupon $coupon, array $couponData): bool
    {
        return $coupon->update([
            'code' => strtoupper($couponData['code']),
            'type' => $couponData['type'],
            'value' => $couponData['value'],
            'cart_value' => $couponData['cart_value'],
            'expiry_date' => $couponData['expiry_date'],
            'usage_limit' => $couponData['usage_limit'] ?? $coupon->usage_limit,
            'is_active' => $couponData['is_active'] ?? $coupon->is_active,
        ]);
    }

    /**
     * Delete coupon
     *
     * @param Coupon $coupon
     * @return bool
     */
    public function deleteCoupon(Coupon $coupon): bool
    {
        return $coupon->delete();
    }

    /**
     * Validate coupon code
     *
     * @param string $code
     * @param float $cartValue
     * @return array
     */
    public function validateCoupon(string $code, float $cartValue): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'كود الخصم غير موجود',
                'coupon' => null
            ];
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'message' => 'كود الخصم غير نشط',
                'coupon' => null
            ];
        }

        // Check expiry date
        if ($coupon->expiry_date < Carbon::today()) {
            return [
                'valid' => false,
                'message' => 'كود الخصم منتهي الصلاحية',
                'coupon' => null
            ];
        }

        // Check minimum cart value
        if ($coupon->cart_value > $cartValue) {
            return [
                'valid' => false,
                'message' => "الحد الأدنى لقيمة السلة هو {$coupon->cart_value}",
                'coupon' => null
            ];
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'message' => 'تم استنفاد عدد مرات استخدام هذا الكوبون',
                'coupon' => null
            ];
        }

        return [
            'valid' => true,
            'message' => 'كود الخصم صالح',
            'coupon' => $coupon
        ];
    }

    /**
     * Calculate discount amount
     *
     * @param Coupon $coupon
     * @param float $cartValue
     * @return float
     */
    public function calculateDiscount(Coupon $coupon, float $cartValue): float
    {
        if ($coupon->type === 'fixed') {
            return min($coupon->value, $cartValue); // Don't exceed cart value
        } else {
            return ($cartValue * $coupon->value) / 100;
        }
    }

    /**
     * Apply coupon usage (increment used count)
     *
     * @param Coupon $coupon
     * @return bool
     */
    public function applyCouponUsage(Coupon $coupon): bool
    {
        $coupon->increment('used_count');
        return true;
    }

    /**
     * Get active coupons
     *
     * @return Collection
     */
    public function getActiveCoupons(): Collection
    {
        return Coupon::where('is_active', true)
            ->where('expiry_date', '>=', Carbon::today())
            ->orderBy('expiry_date', 'ASC')
            ->get();
    }

    /**
     * Get expired coupons
     *
     * @return Collection
     */
    public function getExpiredCoupons(): Collection
    {
        return Coupon::where('expiry_date', '<', Carbon::today())
            ->orderBy('expiry_date', 'DESC')
            ->get();
    }

    /**
     * Get coupons expiring soon
     *
     * @param int $days
     * @return Collection
     */
    public function getCouponsExpiringSoon(int $days = 7): Collection
    {
        $endDate = Carbon::today()->addDays($days);
        
        return Coupon::where('is_active', true)
            ->whereBetween('expiry_date', [Carbon::today(), $endDate])
            ->orderBy('expiry_date', 'ASC')
            ->get();
    }

    /**
     * Get coupon statistics
     *
     * @return array
     */
    public function getCouponStatistics(): array
    {
        $totalCoupons = Coupon::count();
        $activeCoupons = Coupon::where('is_active', true)->count();
        $expiredCoupons = Coupon::where('expiry_date', '<', Carbon::today())->count();
        $usedCoupons = Coupon::where('used_count', '>', 0)->count();

        return [
            'total_coupons' => $totalCoupons,
            'active_coupons' => $activeCoupons,
            'expired_coupons' => $expiredCoupons,
            'used_coupons' => $usedCoupons,
            'unused_coupons' => $totalCoupons - $usedCoupons,
            'expiring_soon' => $this->getCouponsExpiringSoon()->count(),
        ];
    }

    /**
     * Generate unique coupon code
     *
     * @param int $length
     * @return string
     */
    public function generateCouponCode(int $length = 8): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    /**
     * Deactivate expired coupons
     *
     * @return int Number of deactivated coupons
     */
    public function deactivateExpiredCoupons(): int
    {
        return Coupon::where('is_active', true)
            ->where('expiry_date', '<', Carbon::today())
            ->update(['is_active' => false]);
    }

    /**
     * Get most used coupons
     *
     * @param int $limit
     * @return Collection
     */
    public function getMostUsedCoupons(int $limit = 10): Collection
    {
        return Coupon::where('used_count', '>', 0)
            ->orderBy('used_count', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get coupon usage report
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getCouponUsageReport(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth();
        $endDate = $endDate ?: Carbon::now()->endOfMonth();

        $query = Coupon::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'total_created' => $query->count(),
            'total_used' => $query->where('used_count', '>', 0)->count(),
            'total_usage_count' => $query->sum('used_count'),
            'most_used' => $query->where('used_count', '>', 0)
                ->orderBy('used_count', 'DESC')
                ->limit(5)
                ->get(['code', 'used_count', 'type', 'value']),
        ];
    }

    /**
     * Bulk create coupons
     *
     * @param array $couponsData
     * @return array
     */
    public function bulkCreateCoupons(array $couponsData): array
    {
        $created = [];
        $errors = [];

        foreach ($couponsData as $index => $couponData) {
            try {
                $created[] = $this->createCoupon($couponData);
            } catch (\Exception $e) {
                $errors[$index] = $e->getMessage();
            }
        }

        return [
            'created' => $created,
            'errors' => $errors,
            'success_count' => count($created),
            'error_count' => count($errors)
        ];
    }

    /**
     * Check if coupon code exists
     *
     * @param string $code
     * @param int|null $excludeId
     * @return bool
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $query = Coupon::where('code', strtoupper($code));
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}
