<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'session_id',
        'hours',
        'person_type',
    ];

    protected $casts = [
        'hours' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }
}
