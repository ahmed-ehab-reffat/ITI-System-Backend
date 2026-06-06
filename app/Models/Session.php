<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Session extends Model
{
    use HasUuids;

    protected $fillable = ['engagement_id', 'session_date', 'is_delivered'];

    protected $casts = [
        'session_date' => 'date',
        'is_delivered' => 'boolean',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function billingRecord(): HasOne
    {
        return $this->hasOne(BillingRecord::class);
    }
}
