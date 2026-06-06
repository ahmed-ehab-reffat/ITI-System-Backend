<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name','email','password','role','expires_at','compensation_type','fixed_salary','hourly_rate'])]
#[Hidden(['password','remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expires_at' => 'datetime',
        ];
    }

    // Role helpers
    public function isBranchManager(): bool { return $this->role === 'branch_manager'; }
    public function isTrackAdmin(): bool    { return $this->role === 'track_admin'; }
    public function isInstructor(): bool    { return $this->role === 'instructor'; }
    public function isStudent(): bool       { return $this->role === 'student'; }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Relationships
    public function managedCohorts(): BelongsToMany
    {
        return $this->belongsToMany(Cohort::class, 'cohort_track_admin');
    }

    public function labGroups(): BelongsToMany
    {
        return $this->belongsToMany(LabGroup::class, 'lab_group_student');
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class, 'instructor_id');
    }

    public function attendanceLedger(): HasOne
    {
        return $this->hasOne(AttendanceLedger::class, 'student_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function courseGrades(): HasMany
    {
        return $this->hasMany(CourseGrade::class, 'student_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(StudentTag::class, 'student_id');
    }
}
