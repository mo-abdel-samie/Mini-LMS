<?php

namespace Modules\Enrollment\Repositories;

use Illuminate\Support\Collection;
use Modules\Enrollment\Models\Enrollment;

class EloquentEnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function findById(int $id): ?Enrollment
    {
        return Enrollment::find($id);
    }

    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment
    {
        return Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
    }

    public function create(array $data): Enrollment
    {
        return Enrollment::create($data);
    }

    public function firstOrCreate(array $attributes, array $values = []): Enrollment
    {
        return Enrollment::firstOrCreate($attributes, $values);
    }

    public function delete(Enrollment $enrollment): bool
    {
        return (bool) $enrollment->delete();
    }

    public function getByUser(int $userId): Collection
    {
        return Enrollment::query()
            ->with('course')
            ->where('user_id', $userId)
            ->orderByDesc('enrolled_at')
            ->get();
    }
}
