<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Engagement extends Model
{
    use HasUuids;

    protected $fillable = [
        'cohort_id',
        'instructor_id',
        'lab_group_id',
        'type',
        'starts_at',
        'ends_at',
        'hours_per_session',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'hours_per_session' => 'float',
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function labGroup(): BelongsTo
    {
        return $this->belongsTo(LabGroup::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function isActive(): bool
    {
        $today = now()->toDateString();
        return $today >= $this->starts_at && $today <= $this->ends_at;
    }
}
