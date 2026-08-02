<?php

namespace App\Modules\Dashboard\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DashboardPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permission = Permission::findOrCreate(
            'view-dashboard',
            'web'
        );

        $role = Role::findOrCreate(
            'project-user',
            'web'
        );

        $role->givePermissionTo($permission);

        User::query()
            ->whereIn('id', [1, 2])
            ->get()
            ->each(
                fn (User $user) => $user->assignRole($role)
            );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}