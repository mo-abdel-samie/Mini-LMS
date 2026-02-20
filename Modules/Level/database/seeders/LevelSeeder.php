<?php

namespace Modules\Level\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Level\Models\Level;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Beginner', 'slug' => 'beginner'],
            ['name' => 'Intermediate', 'slug' => 'intermediate'],
            ['name' => 'Advanced', 'slug' => 'advanced'],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(
                ['slug' => $level['slug']],
                ['name' => $level['name']]
            );
        }
    }
}
