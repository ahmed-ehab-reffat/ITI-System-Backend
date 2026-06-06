<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    use HasUuids;

    protected $fillable = [
        'track_id',
        'name',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function trackAdmins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cohort_track_admin');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function labGroups(): HasMany
    {
        return $this->hasMany(LabGroup::class);
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function attendanceLedgers(): HasMany
    {
        return $this->hasMany(AttendanceLedger::class);
    }
}
