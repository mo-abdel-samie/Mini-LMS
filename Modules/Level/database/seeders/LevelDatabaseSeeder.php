<?php

namespace Modules\Level\Database\Seeders;

use Illuminate\Database\Seeder;

class LevelDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            LevelSeeder::class,
        ]);
    }
}
