<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find record by ID
     *
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find record by ID or fail
     *
     * @param int $id
     * @param array $columns
     * @return Model
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find record by field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findBy(string $field, $value, array $columns = ['*']): ?Model;

    /**
     * Find records by field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Collection
     */
    public function findAllBy(string $field, $value, array $columns = ['*']): Collection;

    /**
     * Create new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update record
     *
     * @param Model $model
     * @param array $data
     * @return bool
     */
    public function update(Model $model, array $data): bool;

    /**
     * Update record by ID
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateById(int $id, array $data): bool;

    /**
     * Delete record
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool;

    /**
     * Delete record by ID
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;

    /**
     * Count records
     *
     * @return int
     */
    public function count(): int;

    /**
     * Check if record exists
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Get records with relationships
     *
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function with(array $relations, array $columns = ['*']): Collection;

    /**
     * Get records where field is in array
     *
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return Collection
     */
    public function whereIn(string $field, array $values, array $columns = ['*']): Collection;

    /**
     * Get records where field is not in array
     *
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return Collection
     */
    public function whereNotIn(string $field, array $values, array $columns = ['*']): Collection;

    /**
     * Get records where field is between values
     *
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return Collection
     */
    public function whereBetween(string $field, array $values, array $columns = ['*']): Collection;

    /**
     * Get first record
     *
     * @param array $columns
     * @return Model|null
     */
    public function first(array $columns = ['*']): ?Model;

    /**
     * Get latest records
     *
     * @param int $limit
     * @param string $column
     * @param array $columns
     * @return Collection
     */
    public function latest(int $limit = 10, string $column = 'created_at', array $columns = ['*']): Collection;

    /**
     * Get oldest records
     *
     * @param int $limit
     * @param string $column
     * @param array $columns
     * @return Collection
     */
    public function oldest(int $limit = 10, string $column = 'created_at', array $columns = ['*']): Collection;
}
