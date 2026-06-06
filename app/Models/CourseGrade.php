<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseGrade extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_id',
        'student_id',
        'exam_raw_score',
        'exam_raw_max',
        'computed_score',
    ];

    protected $casts = [
        'exam_raw_score' => 'float',
        'exam_raw_max'   => 'float',
        'computed_score' => 'float',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(GradeOverride::class);
    }

    public function latestOverride(): HasOne
    {
        return $this->hasOne(GradeOverride::class)->latestOfMany();
    }
}
