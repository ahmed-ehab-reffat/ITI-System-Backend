<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasUuids;

    protected $fillable = [
        'cohort_id',
        'name',
        'lab_weight',
        'exam_weight',
    ];

    protected $casts = [
        'lab_weight' => 'float',
        'exam_weight' => 'float',
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function labGroups(): HasMany
    {
        return $this->hasMany(LabGroup::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(CourseGrade::class);
    }
}
