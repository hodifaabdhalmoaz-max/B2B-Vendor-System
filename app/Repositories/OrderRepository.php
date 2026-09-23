<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    protected $cacheService;

    /**
     * OrderRepository constructor
     */
    public function __construct(Order $model, CacheService $cacheService)
    {
        parent::__construct($model);
        $this->cacheService = $cacheService;
    }

    /**
     * Get orders by user
     */
    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->with(['orderItems.product', 'transaction'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by status
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('status', $status)
            ->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by multiple statuses
     */
    public function getByStatuses(array $statuses, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereIn('status', $statuses)
            ->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders between dates
     */
    public function getBetweenDates(string $startDate, string $endDate, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by date range and status
     */
    public function getByDateRangeAndStatus(string $startDate, string $endDate, string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', $status)
            ->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent orders
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders with items
     */
    public function getWithItems(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders with user
     */
    public function getWithUser(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders with transactions
     */
    public function getWithTransactions(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['transaction'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by total amount range
     */
    public function getByTotalRange(float $minAmount, float $maxAmount, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereBetween('total', [$minAmount, $maxAmount])
            ->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get pending orders
     */
    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByStatus('ordered', $perPage);
    }

    /**
     * Get processing orders
     */
    public function getProcessing(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByStatus('processing', $perPage);
    }

    /**
     * Get shipped orders
     */
    public function getShipped(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByStatus('shipped', $perPage);
    }

    /**
     * Get delivered orders
     */
    public function getDelivered(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByStatus('delivered', $perPage);
    }

    /**
     * Get cancelled orders
     */
    public function getCancelled(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByStatus('canceled', $perPage);
    }

    /**
     * Get orders statistics
     */
    public function getStatistics(): array
    {
        return $this->cacheService->remember(
            'orders_statistics',
            fn () => [
                'total_orders' => $this->count(),
                'pending_orders' => $this->model->where('status', 'ordered')->count(),
                'processing_orders' => $this->model->where('status', 'processing')->count(),
                'shipped_orders' => $this->model->where('status', 'shipped')->count(),
                'delivered_orders' => $this->model->where('status', 'delivered')->count(),
                'cancelled_orders' => $this->model->where('status', 'canceled')->count(),
                'total_revenue' => $this->model->where('status', 'delivered')->sum('total'),
                'pending_revenue' => $this->model->whereIn('status', ['ordered', 'processing', 'shipped'])->sum('total'),
                'today_orders' => $this->model->whereDate('created_at', Carbon::today())->count(),
                'this_month_orders' => $this->model->whereMonth('created_at', Carbon::now()->month)->count(),
                'this_year_orders' => $this->model->whereYear('created_at', Carbon::now()->year)->count(),
            ],
            'short', // 5 minutes cache for statistics
            [CacheService::CACHE_TAGS['orders'], CacheService::CACHE_TAGS['statistics']]
        );
    }

    /**
     * Get monthly sales data
     */
    public function getMonthlySales(int $year): array
    {
        $monthlySales = $this->model->selectRaw('MONTH(created_at) as month, SUM(total) as total_sales, COUNT(*) as total_orders')
            ->whereYear('created_at', $year)
            ->where('status', 'delivered')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $salesData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData = $monthlySales->firstWhere('month', $i);
            $salesData[] = [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->format('F'),
                'total_sales' => $monthData ? $monthData->total_sales : 0,
                'total_orders' => $monthData ? $monthData->total_orders : 0,
            ];
        }

        return $salesData;
    }

    /**
     * Get daily sales data
     */
    public function getDailySales(string $month): array
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        return $this->model->selectRaw('DATE(created_at) as date, SUM(total) as total_sales, COUNT(*) as total_orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Get top customers
     */
    public function getTopCustomers(int $limit = 10): Collection
    {
        return $this->model->selectRaw('user_id, COUNT(*) as total_orders, SUM(total) as total_spent')
            ->with('user')
            ->where('status', 'delivered')
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get orders by payment method
     */
    public function getByPaymentMethod(string $paymentMethod, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereHas('transaction', function ($query) use ($paymentMethod) {
            $query->where('mode', $paymentMethod);
        })
            ->with(['user', 'transaction'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by city
     */
    public function getByCity(string $city, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('city', $city)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by state
     */
    public function getByState(string $state, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('state', $state)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update order status
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $updateData = ['status' => $status];

        // Set appropriate dates based on status
        switch ($status) {
            case 'delivered':
                $updateData['delivered_date'] = Carbon::now();
                break;
            case 'canceled':
                $updateData['canceled_date'] = Carbon::now();
                break;
        }

        $result = $this->updateById($orderId, $updateData);
        if ($result) {
            $this->invalidateOrderCache();
        }

        return $result;
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(int $orderId): bool
    {
        return $this->updateStatus($orderId, 'delivered');
    }

    /**
     * Mark order as cancelled
     */
    public function markAsCancelled(int $orderId, ?string $reason = null): bool
    {
        $updateData = [
            'status' => 'canceled',
            'canceled_date' => Carbon::now(),
        ];

        if ($reason) {
            $updateData['cancellation_reason'] = $reason;
        }

        $result = $this->updateById($orderId, $updateData);
        if ($result) {
            $this->invalidateOrderCache();
        }

        return $result;
    }

    /**
     * Get orders that need attention (old pending orders)
     */
    public function getNeedingAttention(int $days = 3, int $perPage = 15): LengthAwarePaginator
    {
        $cutoffDate = Carbon::now()->subDays($days);

        return $this->model->where('status', 'ordered')
            ->where('created_at', '<=', $cutoffDate)
            ->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get revenue by date range
     */
    public function getRevenueByDateRange(string $startDate, string $endDate): float
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->sum('total');
    }

    /**
     * Get orders count by status
     */
    public function getCountByStatus(): array
    {
        $counts = $this->model->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present
        $statuses = ['ordered', 'processing', 'shipped', 'delivered', 'canceled'];
        $result = [];

        foreach ($statuses as $status) {
            $result[$status] = $counts[$status] ?? 0;
        }

        return $result;
    }

    /**
     * Search orders
     */
    public function search(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where('id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('state', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        })
            ->with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders with filters
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->newQuery();

        // Status filter
        if (! empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Date range filter
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        // Total amount range filter
        if (! empty($filters['min_total']) && ! empty($filters['max_total'])) {
            $query->whereBetween('total', [$filters['min_total'], $filters['max_total']]);
        }

        // City filter
        if (! empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // State filter
        if (! empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        // Payment method filter
        if (! empty($filters['payment_method'])) {
            $query->whereHas('transaction', function ($q) use ($filters) {
                $q->where('mode', $filters['payment_method']);
            });
        }

        // User filter
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->with(['user', 'orderItems.product', 'transaction'])
            ->paginate($perPage);
    }

    /**
     * Create new order and invalidate cache
     */
    public function create(array $data): Order
    {
        $order = parent::create($data);
        $this->invalidateOrderCache();

        return $order;
    }

    /**
     * Update order and invalidate cache
     *
     * @param  Order  $order
     */
    public function update($order, array $data): bool
    {
        $result = parent::update($order, $data);
        if ($result) {
            $this->invalidateOrderCache();
        }

        return $result;
    }

    /**
     * Invalidate all order-related cache
     */
    public function invalidateOrderCache(): void
    {
        $this->cacheService->flushTags([
            CacheService::CACHE_TAGS['orders'],
            CacheService::CACHE_TAGS['statistics'],
        ]);
    }
}
