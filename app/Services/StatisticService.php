<?php

namespace App\Services;

use App\Models\Statistic;
use Illuminate\Support\Facades\Auth;

class StatisticService
{
    /**
     * Log a page view.
     *
     * @param string $page
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logPageView($page, $data = [])
    {
        return $this->log('page_view', null, null, array_merge(['page' => $page], $data));
    }
    
    /**
     * Log a product view.
     *
     * @param int $productId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logProductView($productId, $data = [])
    {
        return $this->log('product_view', $productId, 'App\Models\Product', $data);
    }
    
    /**
     * Log a category view.
     *
     * @param int $categoryId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logCategoryView($categoryId, $data = [])
    {
        return $this->log('category_view', $categoryId, 'App\Models\Category', $data);
    }
    
    /**
     * Log a search.
     *
     * @param string $query
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logSearch($query, $data = [])
    {
        return $this->log('search', null, null, array_merge(['query' => $query], $data));
    }
    
    /**
     * Log a cart add.
     *
     * @param int $productId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logCartAdd($productId, $data = [])
    {
        return $this->log('cart_add', $productId, 'App\Models\Product', $data);
    }
    
    /**
     * Log a cart remove.
     *
     * @param int $productId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logCartRemove($productId, $data = [])
    {
        return $this->log('cart_remove', $productId, 'App\Models\Product', $data);
    }
    
    /**
     * Log a wishlist add.
     *
     * @param int $productId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logWishlistAdd($productId, $data = [])
    {
        return $this->log('wishlist_add', $productId, 'App\Models\Product', $data);
    }
    
    /**
     * Log a wishlist remove.
     *
     * @param int $productId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logWishlistRemove($productId, $data = [])
    {
        return $this->log('wishlist_remove', $productId, 'App\Models\Product', $data);
    }
    
    /**
     * Log a checkout.
     *
     * @param int $orderId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logCheckout($orderId, $data = [])
    {
        return $this->log('checkout', $orderId, 'App\Models\Order', $data);
    }
    
    /**
     * Log a payment.
     *
     * @param int $orderId
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function logPayment($orderId, $data = [])
    {
        return $this->log('payment', $orderId, 'App\Models\Order', $data);
    }
    
    /**
     * Log a statistic.
     *
     * @param string $type
     * @param int|null $referenceId
     * @param string|null $referenceType
     * @param array $data
     * @return \App\Models\Statistic
     */
    public function log($type, $referenceId = null, $referenceType = null, $data = [])
    {
        return Statistic::create([
            'type' => $type,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
        ]);
    }
    
    /**
     * Get statistics by type.
     *
     * @param string $type
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByType($type, $limit = 10)
    {
        return Statistic::ofType($type)->latest()->limit($limit)->get();
    }
    
    /**
     * Get statistics by date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDateRange($startDate, $endDate, $limit = 10)
    {
        return Statistic::inDateRange($startDate, $endDate)->latest()->limit($limit)->get();
    }
    
    /**
     * Get statistics by type and date range.
     *
     * @param string $type
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTypeAndDateRange($type, $startDate, $endDate, $limit = 10)
    {
        return Statistic::ofType($type)->inDateRange($startDate, $endDate)->latest()->limit($limit)->get();
    }
}
