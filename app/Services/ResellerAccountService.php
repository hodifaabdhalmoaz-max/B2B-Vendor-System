<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ResellerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ResellerAccountService
{
    public function __construct(private readonly AuditService $auditService) {}

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'mobile' => $data['mobile'],
                'password' => $data['password'],
                'utype' => User::TYPE_RESELLER,
                'is_active' => true,
                'force_password_change' => true,
            ]);

            $user->resellerProfile()->create([
                'business_name' => $data['business_name'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'governorate' => $data['governorate'] ?? null,
                'group_name' => $data['group_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => ResellerProfile::STATUS_ACTIVE,
                'reservation_enabled' => (bool) ($data['reservation_enabled'] ?? true),
                'reservation_timeout_minutes' => $data['reservation_timeout_minutes'] ?? null,
            ]);

            $this->auditService->log('reseller_created', [
                'reseller_user_id' => $user->id,
                'username' => $user->username,
            ]);

            return $user->load('resellerProfile');
        });
    }

    public function update(User $reseller, array $data): User
    {
        $this->assertReseller($reseller);

        return DB::transaction(function () use ($reseller, $data): User {
            $reseller->update([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'mobile' => $data['mobile'],
            ]);

            $reseller->resellerProfile->update([
                'business_name' => $data['business_name'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'governorate' => $data['governorate'] ?? null,
                'group_name' => $data['group_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'reservation_enabled' => (bool) ($data['reservation_enabled'] ?? false),
                'reservation_timeout_minutes' => $data['reservation_timeout_minutes'] ?? null,
            ]);

            $this->auditService->log('reseller_updated', [
                'reseller_user_id' => $reseller->id,
                'username' => $reseller->username,
            ]);

            return $reseller->refresh()->load('resellerProfile');
        });
    }

    public function suspend(User $reseller): User
    {
        $this->assertReseller($reseller);

        return DB::transaction(function () use ($reseller): User {
            $reseller->update(['is_active' => false]);
            $reseller->resellerProfile->update(['status' => ResellerProfile::STATUS_INACTIVE]);
            $reseller->tokens()->delete();
            $this->deleteSessionsFor($reseller);

            $this->auditService->log('reseller_suspended', [
                'reseller_user_id' => $reseller->id,
                'username' => $reseller->username,
            ]);

            return $reseller->refresh()->load('resellerProfile');
        });
    }

    public function reactivate(User $reseller): User
    {
        $this->assertReseller($reseller);

        return DB::transaction(function () use ($reseller): User {
            $reseller->update(['is_active' => true]);
            $reseller->resellerProfile->update(['status' => ResellerProfile::STATUS_ACTIVE]);

            $this->auditService->log('reseller_reactivated', [
                'reseller_user_id' => $reseller->id,
                'username' => $reseller->username,
            ]);

            return $reseller->refresh()->load('resellerProfile');
        });
    }

    public function resetTemporaryPassword(User $reseller, string $password): User
    {
        $this->assertReseller($reseller);

        return DB::transaction(function () use ($reseller, $password): User {
            $reseller->update([
                'password' => $password,
                'force_password_change' => true,
            ]);

            $reseller->tokens()->delete();
            $this->deleteSessionsFor($reseller);

            $this->auditService->log('reseller_password_reset', [
                'reseller_user_id' => $reseller->id,
                'username' => $reseller->username,
            ]);

            return $reseller->refresh()->load('resellerProfile');
        });
    }

    public function deleteIfSafe(User $reseller): void
    {
        $this->assertReseller($reseller);

        DB::transaction(function () use ($reseller): void {
            if ($this->hasBusinessHistory($reseller)) {
                throw ValidationException::withMessages([
                    'reseller' => __('This reseller has business history. Suspend the account instead.'),
                ]);
            }

            $profile = $reseller->resellerProfile;
            $resellerId = $reseller->id;
            $username = $reseller->username;

            $profile?->delete();
            $reseller->delete();

            $this->auditService->log('reseller_deleted', [
                'reseller_user_id' => $resellerId,
                'username' => $username,
            ]);
        });
    }

    public function hasBusinessHistory(User $reseller): bool
    {
        $this->assertReseller($reseller);

        $hasOrders = Order::withTrashed()
            ->where('user_id', $reseller->id)
            ->exists();

        return $hasOrders || $reseller->resellerProfile->reservations()->exists();
    }

    private function assertReseller(User $reseller): void
    {
        $reseller->loadMissing('resellerProfile');

        abort_unless($reseller->isReseller() && $reseller->resellerProfile, 404);
    }

    private function deleteSessionsFor(User $reseller): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $reseller->id)->delete();
        }
    }
}
