<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->register($request->validated());
            $token = $this->authService->generateToken($user);

            return response()->json([
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login and get auth token.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $token = $this->authService->login(
            $request->email,
            $request->password
        );

        if (!$token) {
            return response()->json([
                'message' => 'Invalid credentials',
                'error' => 'Email or password is incorrect',
            ], 401);
        }

        $user = $this->authService->getAuthenticatedUser($token);

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ], 200);
    }

    /**
     * Get current authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = $this->authService->getAuthenticatedUser();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Refresh auth token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = $this->authService->refreshToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token refresh failed',
                'error' => 'Unable to refresh token',
            ], 401);
        }

        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ]);
    }

    /**
     * Logout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}

