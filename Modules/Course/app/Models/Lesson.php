<?php

namespace Modules\Course\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
// use Modules\Course\Database\Factories\LessonFactory;

class Lesson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'order',
        'video_url',
        'is_free_preview',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_free_preview' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Lesson $lesson): void {
            if (! $lesson->course_id) {
                return;
            }

            if (! $lesson->slug || $lesson->isDirty('title')) {
                $lesson->slug = static::generateUniqueSlug(
                    (int) $lesson->course_id,
                    $lesson->title,
                    $lesson->exists ? (int) $lesson->id : null
                );
            }
        });
    }

    protected static function generateUniqueSlug(int $courseId, string $title, ?int $ignoreLessonId = null): string
    {
        $baseSlug = Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'lesson';
        $slug = $baseSlug;
        $counter = 2;

        while (static::query()
            ->where('course_id', $courseId)
            ->where('slug', $slug)
            ->when($ignoreLessonId, fn ($query) => $query->where('id', '!=', $ignoreLessonId))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    // protected static function newFactory(): LessonFactory
    // {
    //     // return LessonFactory::new();
    // }
}
