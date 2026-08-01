<?php

namespace App\Modules\Authentication\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [
            [
                'name'              => 'test User 1 ',
                'email'             => 'test1@example.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'test User 2',
                'email'             => 'test2@example.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}