<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Course\Models\Lesson;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['lessons'] = $this->record
            ->lessons()
            ->orderBy('order')
            ->get()
            ->map(fn (Lesson $lesson): array => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'video_url' => $lesson->video_url,
                'is_free_preview' => (bool) $lesson->is_free_preview,
            ])
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['lessons']);

        return $data;
    }

    protected function afterSave(): void
    {
        $stateLessons = collect($this->data['lessons'] ?? [])
            ->filter()
            ->values();

        $currentCourseLessonIds = Lesson::query()
            ->where('course_id', $this->record->id)
            ->orderBy('order')
            ->pluck('id')
            ->values();

        $submittedLessonIds = $stateLessons
            ->pluck('id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        foreach ($currentCourseLessonIds as $offset => $lessonId) {
            Lesson::query()
                ->whereKey($lessonId)
                ->update(['order' => 100000 + $offset]);
        }

        foreach ($stateLessons as $index => $lessonData) {
            $lessonId = isset($lessonData['id']) ? (int) $lessonData['id'] : null;

            $payload = [
                'title' => (string) ($lessonData['title'] ?? ''),
                'video_url' => (string) ($lessonData['video_url'] ?? ''),
                'is_free_preview' => (bool) ($lessonData['is_free_preview'] ?? false),
                'order' => $index + 1,
            ];

            if ($lessonId !== null && $submittedLessonIds->contains($lessonId)) {
                Lesson::query()->whereKey($lessonId)->update($payload);
                continue;
            }

            Lesson::query()->create([
                ...$payload,
                'course_id' => $this->record->id,
            ]);
        }

        Lesson::query()
            ->where('course_id', $this->record->id)
            ->when(
                $submittedLessonIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn('id', $submittedLessonIds->all())
            )
            ->when(
                $submittedLessonIds->isEmpty(),
                fn ($query) => $query
            )
            ->delete();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
