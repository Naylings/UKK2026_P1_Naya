<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyUsers  extends Seeder
{
    public function run(): void
    {
        $users = [];
        $userDetails = [];

        for ($i = 1; $i <= 50; $i++) {
            $userId = $i + 3; // mulai dari id 4

            $users[] = [
                'id' => $userId,
                'email' => "dummy{$i}@ukk2026.com",
                'password' => Hash::make('password'),
                'role' => 'User',
                'credit_score' => rand(0, 100),
                'is_restricted' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $userDetails[] = [
                'nik' => '317401010101' . str_pad($userId, 4, '0', STR_PAD_LEFT),
                'user_id' => $userId,
                'name' => "Dummy User {$i}",
                'no_hp' => '08' . rand(1111111111, 9999999999),
                'address' => ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta'][array_rand(['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta'])],
                'birth_date' => now()->subYears(rand(18, 40))->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($users);
        DB::table('user_details')->insert($userDetails);
    }
    
    
}