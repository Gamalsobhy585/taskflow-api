<?php

namespace App\Modules\Tasks\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TaskPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [
            'create-task',
            'list-task',
            'view-task',
            'update-task',
            'delete-task',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate(
                $permission,
                'web'
            );
        }

        $role = Role::findOrCreate(
            'project-user',
            'web'
        );

        $role->givePermissionTo($permissions);

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