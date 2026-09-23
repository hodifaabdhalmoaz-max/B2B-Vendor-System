<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get orders by user
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get orders by status
     *
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by multiple statuses
     *
     * @param array $statuses
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByStatuses(array $statuses, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders between dates
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBetweenDates(string $startDate, string $endDate, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by date range and status
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByDateRangeAndStatus(string $startDate, string $endDate, string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get recent orders
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Get orders with items
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithItems(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders with user
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithUser(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders with transactions
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithTransactions(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by total amount range
     *
     * @param float $minAmount
     * @param float $maxAmount
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByTotalRange(float $minAmount, float $maxAmount, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get pending orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPending(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get processing orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProcessing(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get shipped orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getShipped(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get delivered orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDelivered(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get cancelled orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCancelled(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders statistics
     *
     * @return array
     */
    public function getStatistics(): array;

    /**
     * Get monthly sales data
     *
     * @param int $year
     * @return array
     */
    public function getMonthlySales(int $year): array;

    /**
     * Get daily sales data
     *
     * @param string $month
     * @return array
     */
    public function getDailySales(string $month): array;

    /**
     * Get top customers
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomers(int $limit = 10): Collection;

    /**
     * Get orders by payment method
     *
     * @param string $paymentMethod
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByPaymentMethod(string $paymentMethod, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by city
     *
     * @param string $city
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCity(string $city, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by state
     *
     * @param string $state
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByState(string $state, int $perPage = 15): LengthAwarePaginator;

    /**
     * Update order status
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $orderId, string $status): bool;

    /**
     * Mark order as delivered
     *
     * @param int $orderId
     * @return bool
     */
    public function markAsDelivered(int $orderId): bool;

    /**
     * Mark order as cancelled
     *
     * @param int $orderId
     * @param string|null $reason
     * @return bool
     */
    public function markAsCancelled(int $orderId, ?string $reason = null): bool;

    /**
     * Get orders that need attention (old pending orders)
     *
     * @param int $days
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getNeedingAttention(int $days = 3, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get revenue by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getRevenueByDateRange(string $startDate, string $endDate): float;

    /**
     * Get orders count by status
     *
     * @return array
     */
    public function getCountByStatus(): array;

    /**
     * Search orders
     *
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $search, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
