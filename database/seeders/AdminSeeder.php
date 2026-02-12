<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'info@smatatech.com.ng'],
            [
                'name' => 'Smatatech Admin',
                'password' => Hash::make('Password@1'),
                'role' => 'super_admin',
                'permissions' => [],
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
