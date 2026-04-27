<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Uses firstOrCreate so re-running the seeder is safe.
     */
    public function run(): void
    {
        // Clear Spatie permission cache before seeding roles
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user',        'guard_name' => 'web']);

        $this->command->info('Roles seeded: super admin, admin, user');
    }
}
