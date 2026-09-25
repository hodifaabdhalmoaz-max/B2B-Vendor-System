<?php

namespace App\Repositories;

use App\Models\InventoryMovement;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/** Read-only operational queries; lifecycle writes belong to ReservationService. */
class AdminReservationRepository
{
    private function filtered(array $filters): Builder
    {
        return Reservation::query()
            ->when($filters['reseller_profile_id'] ?? null, fn (Builder $q, $id) => $q->where('reseller_profile_id', $id))
            ->when($filters['start_date'] ?? null, fn (Builder $q, $date) => $q->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($filters['end_date'] ?? null, fn (Builder $q, $date) => $q->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->when($filters['overdue'] ?? false, fn (Builder $q) => $q
                ->where('status', Reservation::STATUS_PENDING_REVIEW)->whereNotNull('expires_at')->where('expires_at', '<=', now()))
            ->when(isset($filters['search']) && $filters['search'] !== '', function (Builder $q) use ($filters): void {
                $term = '%'.$filters['search'].'%';
                $q->where(function (Builder $searchQuery) use ($term): void {
                    $searchQuery->where('reservation_number', 'like', $term)
                        ->orWhereHas('resellerProfile', function (Builder $profile) use ($term): void {
                            $profile->where(function (Builder $identity) use ($term): void {
                                $identity->where('business_name', 'like', $term)->orWhere('whatsapp', 'like', $term)
                                    ->orWhereHas('user', fn (Builder $user) => $user->where(function (Builder $userIdentity) use ($term): void {
                                        $userIdentity->where('name', 'like', $term)->orWhere('username', 'like', $term)->orWhere('mobile', 'like', $term);
                                    }));
                            });
                        });
                });
            });
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->filtered($filters)
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $q) => $q->where('status', $filters['status']))
            ->with(['resellerProfile.user:id,name,username,mobile', 'reservationItems:id,reservation_id,quantity,unit_price'])
            ->withCount('reservationItems')->latest('created_at')->orderByDesc('id')
            ->paginate(20)->withQueryString();
    }

    /** Counts respect all filters except status, using one grouped query. */
    public function counts(array $filters): array
    {
        return $this->filtered($filters)->selectRaw('status, COUNT(*) AS aggregate')->groupBy('status')->pluck('aggregate', 'status')->all();
    }

    public function reseller(?int $id): ?ResellerProfile
    {
        return $id ? ResellerProfile::with('user:id,name,username')->findOrFail($id) : null;
    }

    public function detail(Reservation $reservation): Reservation
    {
        return $reservation->load(['resellerProfile.user:id,name,username,mobile,is_active', 'reservationItems']);
    }

    public function movements(Reservation $reservation): Collection
    {
        return InventoryMovement::query()->where('reference_type', 'reservation')->where('reference_id', $reservation->id)
            ->with(['productVariant:id,sku', 'actor:id,name'])->orderBy('id')->get();
    }
}
