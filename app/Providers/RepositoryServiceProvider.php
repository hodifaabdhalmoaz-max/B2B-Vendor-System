<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Interfaces
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

// Repository Implementations
use App\Repositories\BaseRepository;
use App\Repositories\ProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

// Models
use App\Models\Product;
use App\Models\Order;
use App\Models\User;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Product Repository
        $this->app->bind(ProductRepositoryInterface::class, function ($app) {
            return new ProductRepository(
                $app->make(Product::class),
                $app->make(\App\Services\CacheService::class)
            );
        });

        // Bind Order Repository
        $this->app->bind(OrderRepositoryInterface::class, function ($app) {
            return new OrderRepository(
                $app->make(Order::class),
                $app->make(\App\Services\CacheService::class)
            );
        });

        // Bind User Repository
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
