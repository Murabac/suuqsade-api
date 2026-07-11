<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingsSeeder::class);
    }

    public function test_send_otp_always_succeeds_in_bypass_mode(): void
    {
        $this->postJson('/api/auth/send-otp', [
            'phone_number' => '252634567890',
        ])
            ->assertOk()
            ->assertJson(['message' => 'OTP sent successfully.']);
    }

    public function test_verify_otp_with_fixed_code_returns_token(): void
    {
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone_number' => '252634567890',
            'code' => '123456',
            'name' => 'Test Customer',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.name', 'Test Customer')
            ->assertJsonPath('user.phone_number', '252634567890');

        $this->assertDatabaseHas('users', [
            'phone_number' => '252634567890',
            'name' => 'Test Customer',
        ]);
    }

    public function test_verify_otp_rejects_invalid_code(): void
    {
        $this->postJson('/api/auth/verify-otp', [
            'phone_number' => '252634567890',
            'code' => '000000',
        ])->assertStatus(422);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.phone_number', $user->phone_number);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully.']);
    }

    public function test_public_settings_endpoint(): void
    {
        $this->getJson('/api/settings/public')
            ->assertOk()
            ->assertJsonStructure([
                'zaad_merchant_number',
                'edahab_merchant_number',
                'default_service_fee_pct',
                'default_shipping_fee',
            ]);
    }

    public function test_admin_can_login(): void
    {
        $this->seed(AdminSeeder::class);

        $this->post('/admin/login', [
            'email' => 'admin@suuqsade.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(Admin::first(), 'admin');
    }
}
