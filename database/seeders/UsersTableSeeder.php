<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // specific demo users
        User::firstOrCreate(
            ['email' => 'demo@meetflow.ai'],
            ['name' => 'Alex Demo', 'password' => Hash::make('password')]
        );

        User::firstOrCreate(
            ['email' => 'sarah@meetflow.ai'],
            ['name' => 'Sarah PM', 'password' => Hash::make('password')]
        );

        User::firstOrCreate(
            ['email' => 'mike@meetflow.ai'],
            ['name' => 'Mike Dev', 'password' => Hash::make('password')]
        );

        User::firstOrCreate(
            ['email' => 'jenna@meetflow.ai'],
            ['name' => 'Jenna Design', 'password' => Hash::make('password')]
        );
    }
}
