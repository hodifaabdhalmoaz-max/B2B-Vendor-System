<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ResellerProfile;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ResellerAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Throwable;

class B2BPhaseTwoAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_reseller_with_user_and_profile(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);

        $response = $this->actingAs($admin)->post(route('admin.resellers.store'), $this->validResellerPayload([
            'email' => null,
        ]));

        $response->assertRedirect();

        $reseller = User::where('username', 'sana-reseller')->firstOrFail();

        $this->assertTrue($reseller->isReseller());
        $this->assertTrue($reseller->is_active);
        $this->assertTrue($reseller->force_password_change);
        $this->assertNull($reseller->email);
        $this->assertDatabaseHas('reseller_profiles', [
            'user_id' => $reseller->id,
            'business_name' => 'Sana Kids Wholesale',
            'status' => ResellerProfile::STATUS_ACTIVE,
            'reservation_enabled' => true,
        ]);
    }

    public function test_reseller_creation_rolls_back_when_profile_creation_fails(): void
    {
        $this->expectException(Throwable::class);

        try {
            app(ResellerAccountService::class)->create($this->validResellerPayload([
                'username' => 'rollback-user',
                'business_name' => ['invalid'],
            ]));
        } finally {
            $this->assertDatabaseMissing('users', ['username' => 'rollback-user']);
            $this->assertSame(0, ResellerProfile::count());
        }
    }

    public function test_username_mobile_and_optional_email_validation(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $existing = $this->createReseller([
            'username' => 'duplicate',
            'mobile' => '777777777',
            'email' => 'reseller@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.resellers.store'), $this->validResellerPayload([
                'username' => 'DUPLICATE',
                'mobile' => '711111111',
                'email' => null,
            ]))
            ->assertSessionHasErrors('username');

        $this->actingAs($admin)
            ->post(route('admin.resellers.store'), $this->validResellerPayload([
                'username' => 'unique-name',
                'mobile' => $existing->mobile,
                'email' => null,
            ]))
            ->assertSessionHasErrors('mobile');

        $this->actingAs($admin)
            ->post(route('admin.resellers.store'), $this->validResellerPayload([
                'username' => 'email-duplicate',
                'mobile' => '733333333',
                'email' => 'reseller@example.com',
            ]))
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)
            ->post(route('admin.resellers.store'), $this->validResellerPayload([
                'username' => 'without-email',
                'mobile' => '744444444',
                'email' => null,
            ]))
            ->assertSessionDoesntHaveErrors();
    }

    public function test_public_registration_is_disabled_and_cannot_self_register_as_reseller(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Public Reseller',
            'username' => 'public-reseller',
            'email' => 'public@example.com',
            'mobile' => '700000001',
            'password' => 'password',
            'password_confirmation' => 'password',
            'utype' => User::TYPE_RESELLER,
        ])->assertNotFound();

        $this->postJson('/api/v1/register', [
            'name' => 'API Reseller',
            'email' => 'api@example.com',
            'mobile' => '700000002',
            'password' => 'password',
            'password_confirmation' => 'password',
            'utype' => User::TYPE_RESELLER,
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['utype' => User::TYPE_RESELLER]);
    }

    public function test_inactive_user_cannot_log_in_through_web_or_api(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'mobile' => '700100100',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();

        $this->postJson('/api/v1/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertUnauthorized();
    }

    public function test_reseller_route_authorization_boundaries(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $legacyUser = User::factory()->create(['utype' => User::TYPE_USER]);
        $activeReseller = $this->createReseller(['force_password_change' => false]);
        $inactiveProfileReseller = $this->createReseller(['username' => 'inactive-profile']);
        $inactiveProfileReseller->resellerProfile->update(['status' => ResellerProfile::STATUS_INACTIVE]);

        $this->actingAs($activeReseller)->get(route('reseller.index'))->assertOk();
        $this->actingAs($admin)->get(route('reseller.index'))->assertForbidden();
        $this->actingAs($legacyUser)->get(route('reseller.index'))->assertForbidden();
        $this->actingAs($inactiveProfileReseller)->get(route('reseller.index'))->assertForbidden();
        $this->actingAs($activeReseller)->get(route('admin.index'))->assertForbidden();
    }

    public function test_reservation_disabled_does_not_block_reseller_login_or_landing_page(): void
    {
        $reseller = $this->createReseller([
            'username' => 'browse-only',
            'password' => Hash::make('password'),
            'force_password_change' => false,
        ]);
        $reseller->resellerProfile->update(['reservation_enabled' => false]);

        $this->post(route('login'), [
            'login' => 'browse-only',
            'password' => 'password',
        ])->assertRedirect(route('reseller.index'));

        $this->get(route('reseller.index'))->assertOk();
    }

    public function test_forced_password_change_flow(): void
    {
        $reseller = $this->createReseller([
            'username' => 'forced-reseller',
            'password' => Hash::make('old-password'),
            'force_password_change' => true,
        ]);

        $this->actingAs($reseller)
            ->get(route('reseller.index'))
            ->assertRedirect(route('reseller.password.edit'));

        $this->actingAs($reseller)
            ->get(route('reseller.password.edit'))
            ->assertOk();

        $this->actingAs($reseller)
            ->post(route('reseller.password.update'), [
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('reseller.index'));

        $reseller->refresh();

        $this->assertFalse($reseller->force_password_change);
        $this->assertNotNull($reseller->password_changed_at);
        $this->assertFalse(Hash::check('old-password', $reseller->password));
        $this->assertTrue(Hash::check('new-secure-password', $reseller->password));
    }

    public function test_api_login_requires_password_change_without_issuing_token(): void
    {
        $reseller = $this->createReseller([
            'username' => 'api-forced',
            'password' => Hash::make('password'),
            'force_password_change' => true,
        ]);

        $this->postJson('/api/v1/login', [
            'login' => $reseller->username,
            'password' => 'password',
        ])
            ->assertStatus(423)
            ->assertJsonPath('error', 'password_change_required');

        $this->assertSame(0, $reseller->tokens()->count());
    }

    public function test_admin_password_reset_sets_force_change_and_revokes_tokens(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $reseller = $this->createReseller([
            'username' => 'reset-reseller',
            'password' => Hash::make('old-password'),
            'force_password_change' => false,
        ]);
        $reseller->createToken('auth_token');

        $this->assertSame(1, $reseller->tokens()->count());

        $this->actingAs($admin)
            ->post(route('admin.resellers.password', $reseller), [
                'password' => 'temporary-password',
                'password_confirmation' => 'temporary-password',
            ])
            ->assertRedirect(route('admin.resellers.edit', $reseller));

        $reseller->refresh();

        $this->assertTrue($reseller->force_password_change);
        $this->assertTrue(Hash::check('temporary-password', $reseller->password));
        $this->assertSame(0, $reseller->tokens()->count());
    }

    public function test_suspending_and_reactivating_reseller_controls_access(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $reseller = $this->createReseller([
            'username' => 'state-reseller',
            'password' => Hash::make('password'),
            'force_password_change' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.resellers.suspend', $reseller))
            ->assertRedirect(route('admin.resellers.index'));

        $reseller->refresh();
        $this->assertFalse($reseller->is_active);
        $this->assertSame(ResellerProfile::STATUS_INACTIVE, $reseller->resellerProfile->status);

        auth()->logout();

        $this->post(route('login'), [
            'login' => 'state-reseller',
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->actingAs($admin)
            ->post(route('admin.resellers.reactivate', $reseller))
            ->assertRedirect(route('admin.resellers.index'));

        $reseller->refresh();
        $this->assertTrue($reseller->is_active);
        $this->assertSame(ResellerProfile::STATUS_ACTIVE, $reseller->resellerProfile->status);
    }

    public function test_reseller_delete_is_blocked_with_history_and_allowed_without_history(): void
    {
        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $withHistory = $this->createReseller(['username' => 'history-reseller']);
        $withReservationHistory = $this->createReseller(['username' => 'reservation-history-reseller']);
        $withoutHistory = $this->createReseller(['username' => 'safe-delete-reseller', 'mobile' => '700200201']);

        Order::create([
            'user_id' => $withHistory->id,
            'subtotal' => '10.00',
            'discount' => '0.00',
            'tax' => '0.00',
            'total' => '10.00',
            'name' => 'History Reseller',
            'phone' => '700200200',
            'locality' => 'Sana',
            'address' => 'Street',
            'city' => 'Sana',
            'state' => 'Sana',
            'country' => 'YE',
            'zip' => '00000',
        ]);

        Reservation::create([
            'reservation_number' => 'RSV-DELETE-BLOCK-001',
            'reseller_profile_id' => $withReservationHistory->resellerProfile->id,
            'status' => Reservation::STATUS_PENDING_REVIEW,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.resellers.destroy', $withHistory))
            ->assertSessionHasErrors('reseller');

        $this->assertDatabaseHas('users', ['id' => $withHistory->id]);

        $this->actingAs($admin)
            ->delete(route('admin.resellers.destroy', $withReservationHistory))
            ->assertSessionHasErrors('reseller');

        $this->assertDatabaseHas('users', ['id' => $withReservationHistory->id]);

        $this->actingAs($admin)
            ->delete(route('admin.resellers.destroy', $withoutHistory))
            ->assertRedirect(route('admin.resellers.index'));

        $this->assertDatabaseMissing('users', ['id' => $withoutHistory->id]);
        $this->assertDatabaseMissing('reseller_profiles', ['user_id' => $withoutHistory->id]);
    }

    private function validResellerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sana Reseller',
            'username' => 'sana-reseller',
            'email' => 'sana-reseller@example.com',
            'mobile' => '700100200',
            'password' => 'temporary-password',
            'password_confirmation' => 'temporary-password',
            'business_name' => 'Sana Kids Wholesale',
            'whatsapp' => '700100200',
            'governorate' => 'Sana',
            'group_name' => 'A',
            'notes' => 'Phase 2 account',
            'reservation_enabled' => true,
            'reservation_timeout_minutes' => 45,
        ], $overrides);
    }

    private function createReseller(array $overrides = []): User
    {
        static $sequence = 0;
        $sequence++;

        $user = User::factory()->create(array_merge([
            'username' => 'reseller-'.$sequence,
            'email' => 'reseller-'.$sequence.'@example.com',
            'mobile' => '7002002'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
            'utype' => User::TYPE_RESELLER,
            'is_active' => true,
            'force_password_change' => false,
        ], $overrides));

        $user->resellerProfile()->create([
            'business_name' => 'Wholesale Account',
            'whatsapp' => $user->mobile,
            'governorate' => 'Sana',
            'group_name' => 'A',
            'status' => ResellerProfile::STATUS_ACTIVE,
            'reservation_enabled' => true,
            'reservation_timeout_minutes' => 30,
        ]);

        return $user->load('resellerProfile');
    }
}
