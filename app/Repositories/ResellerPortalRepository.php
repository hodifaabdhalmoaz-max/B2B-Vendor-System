<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Reservation;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ResellerPortalRepository
{
    public function products(int $userId): Builder
    {
        return Product::query()->whereHas('variants', fn ($q) => $q->active())
            ->with(['category', 'variants' => fn ($q) => $q->active()->with(['inventoryItem', 'color', 'size'])])
            ->addSelect(['is_wishlisted' => Wishlist::selectRaw('1')->whereColumn('product_id', 'products.id')->where('user_id', $userId)->limit(1)]);
    }

    public function catalog(int $userId, array $filters): Builder
    {
        return $this->products($userId)
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $term = '%'.$search.'%';
                    $q->where('name', 'like', $term)->orWhere('SKU', 'like', $term)
                        ->orWhereHas('variants', fn ($v) => $v->active()->where('sku', 'like', $term))
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['in_stock'] ?? false, fn ($q) => $q->whereHas('variants', fn ($v) => $v->active()
                ->whereHas('inventoryItem', fn ($i) => $i->whereColumn('stock_on_hand', '>', 'reserved_quantity'))))
            ->when($filters['offers'] ?? false, fn ($q) => $this->offers($q));
    }

    public function offers(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->where('is_offer', true)->orWhere(fn ($p) => $p
            ->where('sale_price', '>', 0)->whereColumn('sale_price', '<', 'regular_price')));
    }

    public function mostRequested(int $userId)
    {
        // Demand includes pending, confirmed, preparing, shipped and completed only.
        $demand = DB::table('reservation_items')->join('reservations', 'reservations.id', '=', 'reservation_items.reservation_id')
            ->join('product_variants', 'product_variants.id', '=', 'reservation_items.product_variant_id')
            ->whereIn('reservations.status', ['pending_review', 'confirmed', 'preparing', 'shipped', 'completed'])
            ->selectRaw('product_variants.product_id, SUM(reservation_items.quantity) as requested_quantity')
            ->groupBy('product_variants.product_id');

        return $this->products($userId)->joinSub($demand, 'demand', 'demand.product_id', '=', 'products.id')
            ->orderByDesc('demand.requested_quantity')->orderByDesc('products.id')->limit(6)->get();
    }

    public function categories()
    {
        return Category::whereHas('products.variants', fn ($q) => $q->active())->orderBy('name')->get(['id', 'name']);
    }

    public function variants(array $ids)
    {
        return ProductVariant::with(['product', 'color', 'size', 'inventoryItem'])->whereKey($ids)->get()->keyBy('id');
    }

    public function reservations(int $profileId): Builder
    {
        return Reservation::where('reseller_profile_id', $profileId)->with('reservationItems');
    }

    public function ownedReservation(int $profileId, int $id, bool $lock = false): Reservation
    {
        return $this->reservations($profileId)->when($lock, fn ($q) => $q->lockForUpdate())->findOrFail($id);
    }

    public function statusCounts(int $profileId): array
    {
        return Reservation::where('reseller_profile_id', $profileId)->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')->pluck('aggregate', 'status')->all();
    }

    public function wishlist(int $userId): Builder
    {
        return $this->products($userId)->whereIn('products.id', Wishlist::select('product_id')->where('user_id', $userId));
    }

    public function saveWishlist(int $userId, int $productId): void
    {
        // The existing unique(user_id, product_id) constraint handles concurrent adds.
        Wishlist::firstOrCreate(['user_id' => $userId, 'product_id' => $productId]);
    }

    public function removeWishlist(int $userId, int $productId): void
    {
        Wishlist::where('user_id', $userId)->where('product_id', $productId)->delete();
    }
}
