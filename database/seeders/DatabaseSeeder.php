<?php

namespace Database\Seeders;

use App\Modules\Authentication\Seeders\UserSeeder;
use App\Modules\Dashboard\Seeders\DashboardPermissionSeeder;
use App\Modules\Projects\Seeders\ProjectPermissionSeeder;
use App\Modules\Projects\Seeders\ProjectSeeder;
use App\Modules\Tasks\Seeders\TaskPermissionSeeder;
use App\Modules\Tasks\Seeders\TaskSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProjectPermissionSeeder::class,
            TaskPermissionSeeder::class,
            DashboardPermissionSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
        ]);
    }
}