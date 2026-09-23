<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by mobile
     *
     * @param string $mobile
     * @return User|null
     */
    public function findByMobile(string $mobile): ?User;

    /**
     * Get users by type
     *
     * @param string $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get admin users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdmins(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get customer users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCustomers(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get verified users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getVerified(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get unverified users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUnverified(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get inactive users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getInactive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users with orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithOrders(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users without orders
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithoutOrders(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users registered between dates
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRegisteredBetween(string $startDate, string $endDate, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get top customers by orders count
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomersByOrders(int $limit = 10): Collection;

    /**
     * Get top customers by spending
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomersBySpending(int $limit = 10): Collection;

    /**
     * Get users by city
     *
     * @param string $city
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCity(string $city, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users by state
     *
     * @param string $state
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByState(string $state, int $perPage = 15): LengthAwarePaginator;

    /**
     * Search users
     *
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $search, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get user statistics
     *
     * @return array
     */
    public function getStatistics(): array;

    /**
     * Get monthly registration data
     *
     * @param int $year
     * @return array
     */
    public function getMonthlyRegistrations(int $year): array;

    /**
     * Update user status
     *
     * @param int $userId
     * @param bool $isActive
     * @return bool
     */
    public function updateStatus(int $userId, bool $isActive): bool;

    /**
     * Verify user email
     *
     * @param int $userId
     * @return bool
     */
    public function verifyEmail(int $userId): bool;

    /**
     * Update user type
     *
     * @param int $userId
     * @param string $type
     * @return bool
     */
    public function updateType(int $userId, string $type): bool;

    /**
     * Get users who haven't logged in for days
     *
     * @param int $days
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getInactiveForDays(int $days, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get recently registered users
     *
     * @param int $days
     * @param int $limit
     * @return Collection
     */
    public function getRecentlyRegistered(int $days = 7, int $limit = 10): Collection;

    /**
     * Get users with most orders
     *
     * @param int $limit
     * @return Collection
     */
    public function getUsersWithMostOrders(int $limit = 10): Collection;

    /**
     * Get users by registration source
     *
     * @param string $source
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByRegistrationSource(string $source, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users with addresses
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithAddresses(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users without addresses
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithoutAddresses(int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if email exists
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool;

    /**
     * Check if mobile exists
     *
     * @param string $mobile
     * @param int|null $excludeId
     * @return bool
     */
    public function mobileExists(string $mobile, ?int $excludeId = null): bool;

    /**
     * Get users count by type
     *
     * @return array
     */
    public function getCountByType(): array;
}
