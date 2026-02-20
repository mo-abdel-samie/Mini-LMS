<?php

namespace Modules\Enrollment\Repositories;

use Illuminate\Support\Collection;
use Modules\Enrollment\Models\Enrollment;

interface EnrollmentRepositoryInterface
{
    public function findById(int $id): ?Enrollment;

    public function findByUserAndCourse(int $userId, int $courseId): ?Enrollment;

    public function create(array $data): Enrollment;

    public function firstOrCreate(array $attributes, array $values = []): Enrollment;

    public function delete(Enrollment $enrollment): bool;

    public function getByUser(int $userId): Collection;
}

