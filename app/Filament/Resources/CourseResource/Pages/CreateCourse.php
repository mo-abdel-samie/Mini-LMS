<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;
use Modules\Course\Models\Lesson;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['lessons']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $lessons = collect($this->data['lessons'] ?? [])->values();

        foreach ($lessons as $index => $lessonData) {
            Lesson::query()->create([
                'course_id' => $this->record->id,
                'title' => (string) ($lessonData['title'] ?? ''),
                'video_url' => (string) ($lessonData['video_url'] ?? ''),
                'is_free_preview' => (bool) ($lessonData['is_free_preview'] ?? false),
                'order' => $index + 1,
            ]);
        }
    }
}
