<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'description'];

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    public function activeCohort(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Cohort::class)->where('status', 'active');
    }
}
