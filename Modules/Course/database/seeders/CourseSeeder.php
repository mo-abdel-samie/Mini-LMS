<?php

namespace Modules\Course\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Course\Models\Course;
use Modules\Course\Models\Lesson;
use Modules\Level\Models\Level;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'level_slug' => 'beginner',
                'title' => 'HTML Basics for Beginners',
                'description' => 'Learn the foundation of HTML and page structure.',
                'image_path' => null,
            ],
            [
                'level_slug' => 'intermediate',
                'title' => 'Laravel MVC in Practice',
                'description' => 'Build structured applications with controllers, models, and views.',
                'image_path' => null,
            ],
            [
                'level_slug' => 'advanced',
                'title' => 'API Architecture with Laravel',
                'description' => 'Design scalable APIs with clean module boundaries.',
                'image_path' => null,
            ],
        ];

        $levelsBySlug = Level::query()
            ->whereIn('slug', array_column($courses, 'level_slug'))
            ->pluck('id', 'slug');

        foreach ($courses as $courseData) {
            $levelId = $levelsBySlug->get($courseData['level_slug']);
            if (! $levelId) {
                continue;
            }

            $course = Course::updateOrCreate(
                ['slug' => Str::slug($courseData['title'])],
                [
                    'level_id' => $levelId,
                    'title' => $courseData['title'],
                    'description' => $courseData['description'],
                    'image_path' => $courseData['image_path'],
                    'is_published' => true,
                ]
            );

            $lessonCount = random_int(2, 3);

            for ($order = 1; $order <= $lessonCount; $order++) {
                $title = "{$course->title} - Lesson {$order}";

                Lesson::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'order' => $order,
                    ],
                    [
                        'title' => $title,
                        'slug' => Str::slug($title),
                        'video_url' => "https://www.youtube.com/watch?v=cSpndmwWmss&list=PLoP3S2S1qTfCVIETOGwaK3lyaL3UKu403",
                        'is_free_preview' => (bool) random_int(0, 1),
                    ]
                );
            }
        }
    }
}
