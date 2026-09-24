<?php

namespace App\Services;

use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Repositories\ResellerPortalRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResellerPortalService
{
    public function __construct(
        private readonly ResellerPortalRepository $repository,
        private readonly ResellerCatalogService $catalog,
        private readonly ReservationService $reservations,
        private readonly ResellerReservationCartService $cart,
    ) {}

    public function dashboard(ResellerProfile $profile): array
    {
        $recentProducts = $this->repository->products($profile->user_id)->latest('products.created_at')->limit(6)->get();
        $offers = $this->repository->offers($this->repository->products($profile->user_id))->latest('products.id')->limit(6)->get();
        $popular = $this->repository->mostRequested($profile->user_id);
        foreach ([$recentProducts, $offers, $popular] as $products) {
            $products->each(fn ($p) => $this->catalog->present($p));
        }

        return compact('recentProducts', 'offers', 'popular') + [
            'recentReservations' => $this->repository->reservations($profile->id)->latest('id')->limit(5)->get(),
            'statusCounts' => $this->repository->statusCounts($profile->id),
        ];
    }

    public function submit(ResellerProfile $profile, array $items, string $key, ?string $notes): Reservation
    {
        $reservation = $this->reservations->create($profile, $items, $key, $notes);
        $this->cart->clear($profile->id);

        return $reservation;
    }

    public function history(int $profileId)
    {
        return $this->repository->reservations($profileId)->latest('id')->paginate(12);
    }

    public function detail(int $profileId, int $id): Reservation
    {
        return $this->repository->ownedReservation($profileId, $id);
    }

    public function cancel(ResellerProfile $profile, int $id): Reservation
    {
        // Keep the stricter reseller policy atomic with the domain transition.
        // Otherwise confirmation could race between the policy check and cancel().
        return DB::transaction(function () use ($profile, $id) {
            $reservation = $this->repository->ownedReservation($profile->id, $id, true);
            if ($reservation->status !== Reservation::STATUS_PENDING_REVIEW) {
                throw ValidationException::withMessages(['status' => __('Only pending reservations can be cancelled.')]);
            }

            return $this->reservations->cancel($reservation, $profile->user_id);
        });
    }

    public function wishlist(int $userId)
    {
        $products = $this->repository->wishlist($userId)->latest('products.id')->paginate(12);
        $products->getCollection()->each(fn ($p) => $this->catalog->present($p));

        return $products;
    }

    public function saveWishlist(int $userId, int $productId): void
    {
        $this->catalog->product($userId, $productId);
        $this->repository->saveWishlist($userId, $productId);
    }

    public function removeWishlist(int $userId, int $productId): void
    {
        $this->repository->removeWishlist($userId, $productId);
    }
}
