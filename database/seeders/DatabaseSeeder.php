<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin users only
        $admins = [
            [
                'name' => 'Ali Safi Admin',
                'email' => 'alisafisolutionsltd@gmail.com',
                'password' => Hash::make('password'),
                'phone' => '+254110007835',
                'user_type' => 'admin',
                'email_verified_at' => now(),
                'is_verified' => true,
                'is_active' => true,
                'latitude' => -1.2921,
                'longitude' => 36.8219,
            ],
            [
                'name' => 'Kivungi Admin',
                'email' => 'kivungijr@gmail.com',
                'password' => Hash::make('password'),
                'phone' => '+254793049705',
                'user_type' => 'admin',
                'email_verified_at' => now(),
                'is_verified' => true,
                'is_active' => true,
                'latitude' => -1.2921,
                'longitude' => 36.8219,
            ],
        ];

        foreach ($admins as $admin) {
            User::create($admin);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Accounts:');
        $this->command->info('1. Admin: alisafisolutionsltd@gmail.com / password');
        $this->command->info('2. Admin: kivungijr@gmail.com / password');
        $this->command->info('');
        $this->command->info('Note: No vendors, customers, or riders were created.');
        $this->command->info('You can create them through the admin panel or registration forms.');
    }
}