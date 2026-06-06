<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasUuids;

    protected $fillable = [
        'author_id',
        'cohort_id',
        'title',
        'body',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }
}
