<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached permissions so roles are visible right after RoleSeeder
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Super Admin ---
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@larabids.com'],
            [
                'name'              => 'Super Admin',
                'username'          => 'superadmin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles('super admin');

        // --- Admin ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@larabids.com'],
            [
                'name'              => 'Admin',
                'username'          => 'admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles('admin');

        // --- Sample Bidder Users (role: user) ---
        $bidders = [
            ['name' => 'Alice Johnson',  'username' => 'alice',   'email' => 'alice@larabids.com'],
            ['name' => 'Bob Martinez',   'username' => 'bob',     'email' => 'bob@larabids.com'],
            ['name' => 'Carol Singh',    'username' => 'carol',   'email' => 'carol@larabids.com'],
            ['name' => 'David Khan',     'username' => 'david',   'email' => 'david@larabids.com'],
            ['name' => 'Eva Williams',   'username' => 'eva',     'email' => 'eva@larabids.com'],
        ];

        foreach ($bidders as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'username'          => $data['username'],
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles('user');
        }

        $this->command->info('Users seeded (super admin, admin, 5 bidders).');
    }
}

