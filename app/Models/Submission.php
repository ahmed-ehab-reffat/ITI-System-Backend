<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id',
        'student_id',
        'url',
        'file_path',
        'submitted_at',
        'raw_score',
        'late_penalty',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'raw_score'    => 'float',
        'late_penalty' => 'float',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
