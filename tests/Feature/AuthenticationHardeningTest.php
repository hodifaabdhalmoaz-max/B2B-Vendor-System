<?php

namespace Tests\Feature;

use App\Models\ResellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthenticationHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_incorrect_web_password_increments_failed_attempts_once(): void
    {
        $user = $this->createUser();

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $user->refresh();

        $this->assertSame(1, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_repeated_incorrect_web_passwords_lock_account_without_double_incrementing(): void
    {
        $user = $this->createUser();
        $threshold = $user->maxFailedLoginAttempts();

        for ($i = 0; $i < $threshold; $i++) {
            $this->post(route('login'), [
                'login' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $user->refresh();

        $this->assertSame($threshold, $user->failed_login_attempts);
        $this->assertTrue($user->isLocked());
    }

    public function test_locked_web_user_cannot_authenticate_with_correct_password(): void
    {
        $user = $this->createUser([
            'locked_until' => now()->addMinutes(10),
        ]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertTrue($user->fresh()->isLocked());
    }

    public function test_locked_api_user_cannot_receive_token(): void
    {
        $user = $this->createUser([
            'locked_until' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/v1/login', [
            'login' => $user->email,
            'password' => 'password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid login credentials')
            ->assertJsonMissingPath('data.token');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_successful_web_login_clears_failed_attempts_and_updates_login_metadata(): void
    {
        $user = $this->createUser([
            'failed_login_attempts' => 3,
            'locked_until' => now()->subMinute(),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.10'])
            ->post(route('login'), [
                'login' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect('/');

        $user->refresh();

        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertNotNull($user->last_login_at);
        $this->assertSame('10.10.10.10', $user->last_login_ip);
    }

    public function test_successful_api_login_clears_failed_attempts_and_updates_login_metadata(): void
    {
        $user = $this->createUser([
            'failed_login_attempts' => 3,
            'locked_until' => now()->subMinute(),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.11'])
            ->postJson('/api/v1/login', [
                'login' => $user->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token']]);

        $user->refresh();

        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertNotNull($user->last_login_at);
        $this->assertSame('10.10.10.11', $user->last_login_ip);
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_inactive_account_behavior_remains_generic_and_does_not_issue_api_token(): void
    {
        $user = $this->createUser(['is_active' => false]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['login' => trans('auth.failed')]);

        $this->assertGuest();

        $this->postJson('/api/v1/login', [
            'login' => $user->email,
            'password' => 'password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid login credentials');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_inactive_reseller_profile_behavior_remains_generic(): void
    {
        $reseller = $this->createReseller();
        $reseller->resellerProfile->update(['status' => ResellerProfile::STATUS_INACTIVE]);

        $this->post(route('login'), [
            'login' => $reseller->username,
            'password' => 'password',
        ])->assertSessionHasErrors(['login' => trans('auth.failed')]);

        $this->postJson('/api/v1/login', [
            'login' => $reseller->username,
            'password' => 'password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid login credentials');

        $this->assertSame(0, $reseller->tokens()->count());
    }

    public function test_forced_password_change_api_behavior_remains_unchanged(): void
    {
        $reseller = $this->createReseller(['force_password_change' => true]);

        $this->postJson('/api/v1/login', [
            'login' => $reseller->username,
            'password' => 'password',
        ])
            ->assertStatus(423)
            ->assertJsonPath('error', 'password_change_required');

        $this->assertSame(0, $reseller->tokens()->count());
    }

    public function test_public_authentication_failures_use_generic_messages(): void
    {
        $locked = $this->createUser([
            'email' => 'locked@example.com',
            'locked_until' => now()->addMinutes(10),
        ]);
        $inactive = $this->createUser([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $this->post(route('login'), [
            'login' => 'missing@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors(['login' => trans('auth.failed')]);

        $this->post(route('login'), [
            'login' => $locked->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['login' => trans('auth.failed')]);

        $this->post(route('login'), [
            'login' => $inactive->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['login' => trans('auth.failed')]);

        foreach (['missing@example.com', $locked->email, $inactive->email] as $identifier) {
            $this->postJson('/api/v1/login', [
                'login' => $identifier,
                'password' => 'password',
            ])
                ->assertUnauthorized()
                ->assertJsonPath('message', 'Invalid login credentials');
        }
    }

    public function test_expired_lock_allows_successful_login_and_clears_lock_state(): void
    {
        $user = $this->createUser([
            'failed_login_attempts' => 5,
            'locked_until' => now()->subMinute(),
        ]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $user->refresh();

        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_formatted_mobile_can_be_used_with_canonical_login_input(): void
    {
        $reseller = $this->createReseller([
            'mobile' => '+967777123456',
            'force_password_change' => false,
        ]);
        DB::table('users')->where('id', $reseller->id)->update(['mobile' => '+967 777 123 456']);
        $reseller->refresh();

        $this->post(route('login'), [
            'login' => '+967777123456',
            'password' => 'password',
        ])->assertRedirect(route('reseller.index'));

        $this->assertAuthenticatedAs($reseller);
    }

    public function test_email_not_null_rollback_fails_clearly_when_null_email_users_exist(): void
    {
        User::factory()->create([
            'email' => null,
            'mobile' => '700444555',
        ]);

        $migration = require database_path('migrations/2026_09_23_000007_add_phase_two_identity_fields_to_users_table.php');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot restore users.email to NOT NULL while NULL-email users exist.');

        $migration->down();
    }

    public function test_admin_password_reset_revokes_tokens_and_database_sessions(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->create(['utype' => User::TYPE_ADMIN]);
        $reseller = $this->createReseller(['force_password_change' => false]);
        $reseller->createToken('auth_token');
        DB::table('sessions')->insert([
            'id' => 'reseller-session',
            'user_id' => $reseller->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.resellers.password', $reseller), [
                'password' => 'temporary-password',
                'password_confirmation' => 'temporary-password',
            ])
            ->assertRedirect(route('admin.resellers.edit', $reseller));

        $reseller->refresh();

        $this->assertTrue($reseller->force_password_change);
        $this->assertTrue(Hash::check('temporary-password', $reseller->password));
        $this->assertSame(0, PersonalAccessToken::query()->where('tokenable_id', $reseller->id)->count());
        $this->assertDatabaseMissing('sessions', ['id' => 'reseller-session']);
    }

    private function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'user-'.uniqid().'@example.com',
            'mobile' => '700'.random_int(100000, 999999),
            'password' => Hash::make('password'),
            'is_active' => true,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ], $overrides));
    }

    private function createReseller(array $overrides = []): User
    {
        $user = $this->createUser(array_merge([
            'username' => 'reseller-'.uniqid(),
            'utype' => User::TYPE_RESELLER,
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
