<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLedger;
use App\Models\CourseGrade;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    // GET /analytics/student  — ACC-4: student sees ONLY their own data
    public function student(Request $request)
    {
        $student = $request->user();
        abort_unless($student->isStudent(), 403);

        $ledger = $student->attendanceLedger;
        $grades = $student->courseGrades()->with('course')->get();

        return response()->json([
            'attendance_balance' => $ledger?->balance ?? 250,
            'course_grades'      => $grades->map(fn($g) => [
                'course'         => $g->course->name,
                'computed_score' => $g->computed_score,
            ]),
        ]);
    }

    // GET /analytics/instructor  — ACC-3: scoped to their lab group only
    public function instructor(Request $request)
    {
        $instructor = $request->user();
        abort_unless($instructor->isInstructor(), 403);

        $engagements = $instructor->engagements()->with('labGroup.students.submissions')->get();

        $data = $engagements->map(function ($engagement) {
            $students = $engagement->labGroup?->students ?? collect();
            return [
                'engagement_id' => $engagement->id,
                'lab_group'     => $engagement->labGroup?->name,
                'student_count' => $students->count(),
                'submissions'   => $students->flatMap->submissions->count(),
            ];
        });

        return response()->json($data);
    }

    // GET /analytics/at-risk  — ANL-1: ledger < 150 OR any course < 60
    public function atRisk()
    {
        abort_unless(auth()->user()->isTrackAdmin() || auth()->user()->isBranchManager(), 403);

        $lowLedger = AttendanceLedger::where('balance', '<', 150)->pluck('student_id');
        $lowGrade = CourseGrade::where('computed_score', '<', 60)->pluck('student_id');
        $atRiskIds = $lowLedger->merge($lowGrade)->unique();

        $students = User::whereIn('id', $atRiskIds)->get(['id', 'name', 'email']);

        return response()->json($students);
    }

    // GET /analytics/cohorts/{cohort}
    public function cohort(\App\Models\Cohort $cohort)
    {
        abort_unless(auth()->user()->isTrackAdmin() || auth()->user()->isBranchManager(), 403);

        $cohort->load(['labGroups.students.courseGrades', 'labGroups.engagements.instructor']);

        $graderConsistency = $cohort->labGroups->map(function ($group) {
            $mean = $group->students->flatMap->courseGrades->avg('computed_score');
            $instructor = $group->engagements->first()?->instructor;
            return [
                'lab_group'  => $group->name,
                'instructor' => $instructor?->name,
                'mean_score' => round($mean, 2),
            ];
        });

        return response()->json([
            'cohort_id'          => $cohort->id,
            'grader_consistency' => $graderConsistency,
        ]);
    }

    // GET /analytics/branch  — branch_manager cross-track summary
    public function branch()
    {
        abort_unless(auth()->user()->isBranchManager(), 403);

        $tracks = \App\Models\Track::with(['cohorts.labGroups.students.courseGrades'])->get();

        $summary = $tracks->map(fn($track) => [
            'track'         => $track->name,
            'cohort_count'  => $track->cohorts->count(),
            'student_count' => $track->cohorts->flatMap->labGroups->flatMap->students->unique('id')->count(),
            'avg_score'     => round(
                $track->cohorts->flatMap->labGroups->flatMap->students->flatMap->courseGrades->avg('computed_score'),
                2
            ),
        ]);

        return response()->json($summary);
    }
}
