<?php

namespace Modules\Progres\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Course\Models\Lesson;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'started_at',
        'completed_at',
        'watch_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'watch_seconds' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
