<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

// Models
use App\Models\Session;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLedger;
use App\Models\ExcuseRequest;
use App\Models\Submission;
use App\Models\CourseGrade;

// Policies
use App\Policies\SessionPolicy;
use App\Policies\AttendanceRecordPolicy;
use App\Policies\AttendanceLedgerPolicy;
use App\Policies\ExcuseRequestPolicy;
use App\Policies\SubmissionPolicy;
use App\Policies\CourseGradePolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Session::class,           SessionPolicy::class);
        Gate::policy(AttendanceRecord::class,  AttendanceRecordPolicy::class);
        Gate::policy(AttendanceLedger::class,  AttendanceLedgerPolicy::class);
        Gate::policy(ExcuseRequest::class,     ExcuseRequestPolicy::class);
        Gate::policy(Submission::class,        SubmissionPolicy::class);
        Gate::policy(CourseGrade::class,       CourseGradePolicy::class);
    }
}
