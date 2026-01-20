<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for admin guard
        $permissions = [
            // User management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.manage_roles',

            // Posts
            'posts.view',
            'posts.create',
            'posts.update',
            'posts.delete',
            'posts.publish',

            // Categories
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            // Comments
            'comments.view',
            'comments.moderate',
            'comments.delete',

            // Services
            'services.view',
            'services.create',
            'services.update',
            'services.delete',

            // Case Studies
            'case_studies.view',
            'case_studies.create',
            'case_studies.update',
            'case_studies.delete',

            // Testimonials
            'testimonials.view',
            'testimonials.create',
            'testimonials.update',
            'testimonials.delete',

            // Brands
            'brands.view',
            'brands.create',
            'brands.update',
            'brands.delete',

            // Contacts
            'contacts.view',
            'contacts.manage',
            'contacts.delete',

            // Settings
            'settings.view',
            'settings.update',

            // Chatbot
            'chatbot.view',
            'chatbot.update',

            // Email
            'email.view',
            'email.update',
            'email.brevo',

            // Dashboard
            'dashboard.view',

            // Uploads
            'uploads.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Create roles and assign permissions
        
        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $superAdmin->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // Admin - has most permissions except role management and brevo
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $adminPermissions = Permission::where('guard_name', 'admin')
            ->whereNotIn('name', ['users.manage_roles', 'email.brevo'])
            ->get();
        $admin->givePermissionTo($adminPermissions);

        // Editor - content management only
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'admin']);
        $editorPermissions = [
            'posts.view', 'posts.create', 'posts.update', 'posts.publish',
            'categories.view',
            'comments.view', 'comments.moderate',
            'services.view', 'services.update',
            'case_studies.view', 'case_studies.update',
            'testimonials.view', 'testimonials.update',
            'brands.view', 'brands.update',
            'dashboard.view',
            'uploads.manage',
        ];
        $editor->givePermissionTo($editorPermissions);

        // Viewer - read-only access
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'admin']);
        $viewerPermissions = [
            'posts.view',
            'categories.view',
            'comments.view',
            'services.view',
            'case_studies.view',
            'testimonials.view',
            'brands.view',
            'dashboard.view',
        ];
        $viewer->givePermissionTo($viewerPermissions);

        // Create default super admin if doesn't exist
        $defaultAdmin = Admin::firstOrCreate(
            ['email' => 'admin@smatatech.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        
        if (!$defaultAdmin->hasRole('super_admin')) {
            $defaultAdmin->assignRole('super_admin');
        }

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Default admin created: admin@smatatech.com / password');
    }
}
