<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceRecord extends Model
{
    use HasUuids;

    protected $fillable = ['session_id', 'student_id', 'arrived_at', 'left_at', 'status'];

    protected $casts = [
        'arrived_at' => 'datetime',
        'left_at'    => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function excuseRequest(): HasOne
    {
        return $this->hasOne(ExcuseRequest::class);
    }
}
