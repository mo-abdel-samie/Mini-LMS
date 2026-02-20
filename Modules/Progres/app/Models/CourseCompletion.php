<?php

namespace Modules\Progres\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Course\Models\Course;

class CourseCompletion extends Model
{
    protected $table = 'course_completions';

    protected $fillable = [
        'user_id',
        'course_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

