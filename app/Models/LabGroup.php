<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabGroup extends Model
{
    use HasUuids;

    protected $fillable = ['cohort_id', 'name'];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lab_group_student');
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }
}
