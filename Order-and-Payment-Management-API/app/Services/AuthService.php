<?php

namespace App\Services;

use App\Actions\RegisterUserAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * AuthService
 *
 * Service layer for authentication operations.
 * Centralizes all auth-related business logic.
 */
class AuthService
{
    protected RegisterUserAction $registerUserAction;

    public function __construct(RegisterUserAction $registerUserAction)
    {
        $this->registerUserAction = $registerUserAction;
    }

    /**
     * Register a new user.
     *
     * @param array $data User registration data
     * @return User
     */
    public function register(array $data): User
    {
        return $this->registerUserAction->execute($data);
    }

    /**
     * Authenticate a user.
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function login(string $email, string $password): ?string
    {
        try {
            return JWTAuth::attempt(['email' => $email, 'password' => $password]);
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Get the authenticated user.
     *
     * @param string|null $token
     * @return User|null
     */
    public function getAuthenticatedUser(?string $token = null): ?User
    {
        try {
            if ($token !== null) {
                return JWTAuth::setToken($token)->authenticate();
            }

            return JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Logout the current user.
     *
     * @return void
     */
    public function logout(): void
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            // Continue gracefully if token is invalid or missing.
        }
    }

    /**
     * Refresh the current JWT token.
     *
     * @return string|null
     */
    public function refreshToken(): ?string
    {
        try {
            return JWTAuth::refresh();
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Generate an API token for a user.
     *
     * @param User $user
     * @return string
     */
    public function generateToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }
}
