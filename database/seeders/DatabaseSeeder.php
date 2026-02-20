<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Course\Database\Seeders\CourseDatabaseSeeder;
use Modules\Level\Database\Seeders\LevelDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LevelDatabaseSeeder::class,
            CourseDatabaseSeeder::class,
            ShieldSeeder::class,
            SuperAdminSeeder::class,
            UserSeeder::class,
        ]);
    }
}
