<?php

namespace App\Modules\Projects\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProjectPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'create-project',
            'list-project',
            'view-project',
            'update-project',
            'delete-project',
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

        $role->syncPermissions($permissions);

        User::query()
            ->whereIn('id', [1, 2])
            ->get()
            ->each(function (User $user) use ($role): void {
                $user->assignRole($role);
            });
    }
}