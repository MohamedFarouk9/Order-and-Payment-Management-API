<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed users for testing.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $users = [
            ['name' => 'Test User', 'email' => 'test@example.com'],
            ['name' => 'Alice Smith', 'email' => 'alice@example.com'],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
        }

        foreach (range(1, 4) as $_) {
            User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
        }
    }
}
