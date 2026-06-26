<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AuthenticationTest
 *
 * Tests JWT authentication endpoints.
 * Covers registration, login, token refresh, and logout.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration.
     */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test registration with invalid email.
     */
    public function test_registration_fails_with_invalid_email()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test registration with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test user login.
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'expires_in',
                ],
            ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test getting current user.
     */
    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        // For testing, we'll use basic auth instead of JWT
        $response = $this->actingAs($user, 'api')->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'created_at'],
            ]);
    }

    /**
     * Test logout.
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);
    }

    /**
     * Test unauthenticated requests are rejected.
     */
    public function test_unauthenticated_request_is_rejected()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }
}
