<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTag extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'tagged_by',
        'cohort_id',
        'tag_type',
        'tag_value',
        'note',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tagger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tagged_by');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }
}
