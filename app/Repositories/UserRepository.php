<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by mobile
     *
     * @param string $mobile
     * @return User|null
     */
    public function findByMobile(string $mobile): ?User
    {
        return $this->model->where('mobile', $mobile)->first();
    }

    /**
     * Get users by type
     *
     * @param string $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('utype', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get admin users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdmins(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByType('ADM', $perPage);
    }

    /**
     * Get customer users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByType('USR', $perPage);
    }

    /**
     * Get verified users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getVerified(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereNotNull('email_verified_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get unverified users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUnverified(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereNull('email_verified_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get inactive users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getInactive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('is_active', false)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get users with orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithOrders(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->has('orders')
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get users without orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithoutOrders(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->doesntHave('orders')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get users registered between dates
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRegisteredBetween(string $startDate, string $endDate, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get top customers by orders count
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomersByOrders(int $limit = 10): Collection
    {
        return $this->model->withCount('orders')
            ->where('utype', 'USR')
            ->orderBy('orders_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top customers by spending
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomersBySpending(int $limit = 10): Collection
    {
        return $this->model->selectRaw('users.*, SUM(orders.total) as total_spent')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('users.utype', 'USR')
            ->where('orders.status', 'delivered')
            ->groupBy('users.id')
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users by city
     *
     * @param string $city
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCity(string $city, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereHas('addresses', function($query) use ($city) {
            $query->where('city', $city);
        })
        ->with('addresses')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

    /**
     * Get users by state
     *
     * @param string $state
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByState(string $state, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereHas('addresses', function($query) use ($state) {
            $query->where('state', $state);
        })
        ->with('addresses')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

    /**
     * Search users
     *
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
        })
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }

    /**
     * Get users with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->newQuery();

        // User type filter
        if (!empty($filters['utype'])) {
            $query->where('utype', $filters['utype']);
        }

        // Email verification filter
        if (isset($filters['email_verified'])) {
            if ($filters['email_verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Active status filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Registration date range filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        // Has orders filter
        if (isset($filters['has_orders'])) {
            if ($filters['has_orders']) {
                $query->has('orders');
            } else {
                $query->doesntHave('orders');
            }
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get user statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_users' => $this->count(),
            'admin_users' => $this->model->where('utype', 'ADM')->count(),
            'customer_users' => $this->model->where('utype', 'USR')->count(),
            'verified_users' => $this->model->whereNotNull('email_verified_at')->count(),
            'unverified_users' => $this->model->whereNull('email_verified_at')->count(),
            'active_users' => $this->model->where('is_active', true)->count(),
            'inactive_users' => $this->model->where('is_active', false)->count(),
            'users_with_orders' => $this->model->has('orders')->count(),
            'users_without_orders' => $this->model->doesntHave('orders')->count(),
            'today_registrations' => $this->model->whereDate('created_at', Carbon::today())->count(),
            'this_month_registrations' => $this->model->whereMonth('created_at', Carbon::now()->month)->count(),
            'this_year_registrations' => $this->model->whereYear('created_at', Carbon::now()->year)->count(),
        ];
    }

    /**
     * Get monthly registration data
     *
     * @param int $year
     * @return array
     */
    public function getMonthlyRegistrations(int $year): array
    {
        $monthlyRegistrations = $this->model->selectRaw('MONTH(created_at) as month, COUNT(*) as total_registrations')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $registrationData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData = $monthlyRegistrations->firstWhere('month', $i);
            $registrationData[] = [
                'month' => $i,
                'month_name' => Carbon::create()->month($i)->format('F'),
                'total_registrations' => $monthData ? $monthData->total_registrations : 0,
            ];
        }

        return $registrationData;
    }

    /**
     * Update user status
     *
     * @param int $userId
     * @param bool $isActive
     * @return bool
     */
    public function updateStatus(int $userId, bool $isActive): bool
    {
        return $this->updateById($userId, ['is_active' => $isActive]);
    }

    /**
     * Verify user email
     *
     * @param int $userId
     * @return bool
     */
    public function verifyEmail(int $userId): bool
    {
        return $this->updateById($userId, ['email_verified_at' => Carbon::now()]);
    }

    /**
     * Update user type
     *
     * @param int $userId
     * @param string $type
     * @return bool
     */
    public function updateType(int $userId, string $type): bool
    {
        return $this->updateById($userId, ['utype' => $type]);
    }

    /**
     * Get users who haven't logged in for days
     *
     * @param int $days
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getInactiveForDays(int $days, int $perPage = 15): LengthAwarePaginator
    {
        $cutoffDate = Carbon::now()->subDays($days);

        return $this->model->where('last_login_at', '<=', $cutoffDate)
            ->orWhereNull('last_login_at')
            ->orderBy('last_login_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get recently registered users
     *
     * @param int $days
     * @param int $limit
     * @return Collection
     */
    public function getRecentlyRegistered(int $days = 7, int $limit = 10): Collection
    {
        $cutoffDate = Carbon::now()->subDays($days);

        return $this->model->where('created_at', '>=', $cutoffDate)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users with most orders
     *
     * @param int $limit
     * @return Collection
     */
    public function getUsersWithMostOrders(int $limit = 10): Collection
    {
        return $this->model->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users by registration source
     *
     * @param string $source
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByRegistrationSource(string $source, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('registration_source', $source)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get users with addresses
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithAddresses(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->has('addresses')
            ->with('addresses')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get users without addresses
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithoutAddresses(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->doesntHave('addresses')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Check if email exists
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if mobile exists
     *
     * @param string $mobile
     * @param int|null $excludeId
     * @return bool
     */
    public function mobileExists(string $mobile, ?int $excludeId = null): bool
    {
        $query = $this->model->where('mobile', $mobile);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get users count by type
     *
     * @return array
     */
    public function getCountByType(): array
    {
        $counts = $this->model->selectRaw('utype, COUNT(*) as count')
            ->groupBy('utype')
            ->pluck('count', 'utype')
            ->toArray();

        return [
            'ADM' => $counts['ADM'] ?? 0,
            'USR' => $counts['USR'] ?? 0,
        ];
    }
}
