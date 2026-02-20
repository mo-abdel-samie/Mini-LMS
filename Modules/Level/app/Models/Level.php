<?php

namespace Modules\Level\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Course\Models\Course;
// use Modules\Level\Database\Factories\LevelFactory;

class Level extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    // protected static function newFactory(): LevelFactory
    // {
    //     // return LevelFactory::new();
    // }
}
