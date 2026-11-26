<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationAuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_log_in_and_read_identity(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password-123'), 'role' => 'administrator', 'is_active' => true]);
        $login = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password-123']);
        $login->assertOk()->assertJsonPath('data.user.role', 'administrator')->assertJsonStructure(['data' => ['token']]);
        $this->withToken($login->json('data.token'))->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('data.id', $user->id);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password-123'), 'is_active' => false]);
        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password-123'])->assertUnauthorized();
    }

    public function test_advisor_cannot_access_administrator_categories(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'advisor', 'is_active' => true]));
        $this->postJson('/api/v1/categories', ['name' => 'Perforación'])->assertForbidden();
    }

    public function test_protected_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/tools')->assertUnauthorized();
    }
}
