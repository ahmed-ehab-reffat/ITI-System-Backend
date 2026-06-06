<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLedger extends Model
{
    use HasUuids;

    protected $table = 'attendance_ledger';

    protected $fillable = ['student_id', 'cohort_id', 'balance'];

    protected $attributes = [
        'balance' => 250,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }
}
