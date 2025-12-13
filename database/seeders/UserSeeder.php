<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        $user->tokens()->delete();
        $token = $user->createToken('api-token');
        $plainTextToken = $token->plainTextToken;

        if ($this->command) {
            $this->command->info('API Token created: ' . $plainTextToken);
        }
    }
}
