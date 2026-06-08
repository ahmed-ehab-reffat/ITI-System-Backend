<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TrackController;
use App\Http\Controllers\Api\CohortController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LabGroupController;
use App\Http\Controllers\Api\EngagementController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttendanceLedgerController;
use App\Http\Controllers\Api\ExcuseRequestController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\GradeOverrideController;
use App\Http\Controllers\Api\StudentTagController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\QrController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// ── Authenticated ────────────────────────────────────────
Route::middleware(['auth:sanctum', 'account.active'])->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    // Users
    Route::apiResource('users', UserController::class);

    // Tracks
    Route::apiResource('tracks', TrackController::class);

    // Cohorts
    Route::apiResource('cohorts', CohortController::class);

    // Courses (nested under cohorts)
    Route::apiResource('cohorts.courses', CourseController::class)->shallow();

    // Lab Groups
    Route::apiResource('cohorts.lab-groups', LabGroupController::class)->shallow();
    Route::post('lab-groups/{labGroup}/students',          [LabGroupController::class, 'assignStudents']);
    Route::delete('lab-groups/{labGroup}/students/{user}', [LabGroupController::class, 'removeStudent']);

    // Engagements
    Route::apiResource('cohorts.engagements', EngagementController::class)->shallow();

    // Sessions
    Route::apiResource('engagements.sessions', SessionController::class)->shallow();
    Route::patch('sessions/{session}/deliver', [SessionController::class, 'deliver']);

    // Attendance
    Route::get('sessions/{session}/attendance',            [AttendanceController::class, 'index']);
    Route::post('sessions/{session}/attendance',           [AttendanceController::class, 'store']);
    Route::patch('sessions/{session}/attendance/{record}', [AttendanceController::class, 'update']);
    Route::get('students/{user}/attendance',               [AttendanceController::class, 'studentHistory']);

    // Ledger
    Route::get('students/{user}/ledger', [AttendanceLedgerController::class, 'show']);

    // Excuse Requests
    Route::apiResource('excuse-requests', ExcuseRequestController::class)->except(['update', 'destroy']);
    Route::patch('excuse-requests/{excuseRequest}/approve', [ExcuseRequestController::class, 'approve']);
    Route::patch('excuse-requests/{excuseRequest}/reject',  [ExcuseRequestController::class, 'reject']);

    // Submissions
    Route::get('sessions/{session}/submissions',                      [SubmissionController::class, 'index']);
    Route::post('sessions/{session}/submissions',                     [SubmissionController::class, 'store']);
    Route::patch('sessions/{session}/submissions/{submission}/grade', [SubmissionController::class, 'grade']);
    Route::get('students/{user}/submissions',                         [SubmissionController::class, 'studentIndex']);

    // Grades
    Route::get('courses/{course}/grades',         [GradeController::class, 'index']);
    Route::post('courses/{course}/grades',        [GradeController::class, 'store']);
    Route::get('courses/{course}/grades/{grade}', [GradeController::class, 'show']);
    Route::get('students/{user}/grades/summary',  [GradeController::class, 'summary']);

    // Grade Overrides
    Route::get('grades/{grade}/overrides',  [GradeOverrideController::class, 'index']);
    Route::post('grades/{grade}/override',  [GradeOverrideController::class, 'store']);

    // Student Tags
    Route::get('students/{user}/tags',              [StudentTagController::class, 'index']);
    Route::post('students/{user}/tags',             [StudentTagController::class, 'store']);
    Route::delete('students/{user}/tags/{tag}',     [StudentTagController::class, 'destroy']);

    // Announcements
    Route::apiResource('cohorts.announcements', AnnouncementController::class)->shallow();

    // Billing
    Route::get('billing',        [BillingController::class, 'index']);
    Route::get('billing/{user}', [BillingController::class, 'show']);

    // Analytics
    Route::get('analytics/branch',           [AnalyticsController::class, 'branch']);
    Route::get('analytics/cohorts/{cohort}', [AnalyticsController::class, 'cohort']);
    Route::get('analytics/instructor',       [AnalyticsController::class, 'instructor']);
    Route::get('analytics/student',          [AnalyticsController::class, 'student']);
    Route::get('analytics/at-risk',          [AnalyticsController::class, 'atRisk']);

    // QR
    Route::get('qr/session/{session}', [QrController::class, 'generate']);
    Route::post('qr/scan',             [QrController::class, 'scan']);
});
