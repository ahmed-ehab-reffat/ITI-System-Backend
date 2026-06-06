<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeOverride extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_grade_id',
        'overridden_by',
        'original_value',
        'new_value',
        'reason',
    ];

    protected $casts = [
        'original_value' => 'float',
        'new_value'      => 'float',
    ];

    public function courseGrade(): BelongsTo
    {
        return $this->belongsTo(CourseGrade::class);
    }

    public function overriddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
