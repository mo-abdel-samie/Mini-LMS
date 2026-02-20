<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Enrollment\Models\Enrollment;
use Modules\Level\Models\Level;
use Modules\Progres\Models\CourseCompletion;
// use Modules\Course\Database\Factories\CourseFactory;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'level_id',
        'title',
        'slug',
        'description',
        'image_path',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Course $course): void {
            if ($course->is_published && empty($course->published_at)) {
                $course->published_at = Carbon::now();
            }

            if (! $course->is_published) {
                $course->published_at = null;
            }
        });
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(CourseCompletion::class);
    }

    public function getImagePathAttribute($value): ?string
    {
        if (!$value) {
            return 'default-course.png';
        }

        return "storage/$value";
    }
}
