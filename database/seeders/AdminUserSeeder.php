<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default admin user.
     */
    public function run(): void
    {
        // Create super admin (using a different email than RolesAndPermissionsSeeder)
        $superAdmin = Admin::firstOrCreate(
            ['email' => 'admin@smatatech.com.ng'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123456'),
            ]
        );

        // Assign super_admin role
        $superAdminRole = Role::where('name', 'super_admin')->where('guard_name', 'admin')->first();
        if ($superAdminRole && !$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        $this->command->info('Admin user created:');
        $this->command->info('Email: admin@smatatech.com.ng');
        $this->command->info('Password: Admin@123456');
        $this->command->warn('⚠️  Please change the password after first login!');
    }
}
