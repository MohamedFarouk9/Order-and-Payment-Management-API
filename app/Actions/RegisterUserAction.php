<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * RegisterUserAction
 *
 * Registers a new user in the system.
 */
class RegisterUserAction
{
    /**
     * Execute the action.
     *
     * @param array $data User registration data
     * @return User
     * @throws \Exception
     */
    public function execute(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
