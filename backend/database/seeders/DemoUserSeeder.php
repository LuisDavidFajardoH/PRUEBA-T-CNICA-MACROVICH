<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'demo@weatherbot.com'],
            [
                'name' => 'Usuario Demo',
                'email' => 'demo@weatherbot.com',
                'password' => Hash::make('password123'),
                'timezone' => 'Europe/Madrid',
                'preferred_language' => 'es',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Demo user created successfully!');
        $this->command->info('Email: demo@weatherbot.com');
        $this->command->info('Password: password123');
    }
}
