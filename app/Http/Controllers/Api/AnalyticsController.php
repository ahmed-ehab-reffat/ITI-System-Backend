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

        $balance = $ledger?->balance ?? 250;
        $lowLedger = $balance < 150;
        $lowGrades = $grades->filter(fn($g) => $g->computed_score < 60);
        $isAtRisk = $lowLedger || $lowGrades->isNotEmpty();
        
        $atRiskReason = null;
        if ($lowLedger && $lowGrades->isNotEmpty()) {
            $atRiskReason = 'Attendance ledger below 150 and course grade(s) below 60';
        } elseif ($lowLedger) {
            $atRiskReason = 'Attendance ledger below 150';
        } elseif ($lowGrades->isNotEmpty()) {
            $courseNames = $lowGrades->map(fn($g) => $g->course->name)->implode(', ');
            $atRiskReason = 'Course grade below 60 in ' . $courseNames;
        }

        $trend = \App\Models\AttendanceRecord::where('student_id', $student->id)
            ->with('session')
            ->get()
            ->sortByDesc(fn($record) => $record->session->session_date)
            ->take(5)
            ->map(fn($record) => [
                'status' => $record->status,
                'date' => $record->session->session_date
            ])->values();

        return response()->json([
            'attendance_balance' => $balance,
            'is_at_risk'         => $isAtRisk,
            'at_risk_reason'     => $atRiskReason,
            'attendance_trend'   => $trend,
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

        $engagements = $instructor->engagements()->with(['labGroup.students.submissions', 'labGroup.students.courseGrades', 'sessions.billingRecord'])->get();

        $data = $engagements->map(function ($engagement) {
            $students = $engagement->labGroup?->students ?? collect();
            $sessionIds = $engagement->sessions->pluck('id');

            $submissionTracker = $students->map(function ($student) use ($sessionIds) {
                $submission = $student->submissions->whereIn('session_id', $sessionIds)->first();
                return [
                    'student_name' => $student->name,
                    'submitted' => $submission !== null,
                    'days_late' => $submission?->days_late ?? 0,
                    'graded' => $submission !== null && $submission->raw_score !== null,
                ];
            });

            $scores = $students->flatMap->courseGrades->pluck('computed_score');
            $gradeDistribution = [
                'above_90' => $scores->filter(fn($s) => $s >= 90)->count(),
                '70_to_90' => $scores->filter(fn($s) => $s >= 70 && $s < 90)->count(),
                '60_to_70' => $scores->filter(fn($s) => $s >= 60 && $s < 70)->count(),
                'below_60' => $scores->filter(fn($s) => $s < 60)->count(),
            ];

            $deliveredHours = $engagement->sessions->where('is_delivered', true)->flatMap->billingRecords->sum('hours') ?? 0;

            return [
                'engagement_id' => $engagement->id,
                'lab_group'     => $engagement->labGroup?->name,
                'student_count' => $students->count(),
                'submissions'   => $students->flatMap->submissions->count(),
                'grade_distribution' => $gradeDistribution,
                'submission_tracker' => $submissionTracker,
                'delivered_hours' => $deliveredHours,
            ];
        });

        return response()->json($data);
    }

    // GET /analytics/at-risk  — ANL-1: ledger < 150 OR any course < 60
    public function atRisk()
    {
        abort_unless(auth()->user()->isTrackAdmin() || auth()->user()->isBranchManager(), 403);

        $lowLedger = AttendanceLedger::where('balance', '<', 150)->get();
        $lowGrade = CourseGrade::where('computed_score', '<', 60)->with('course')->get();
        
        $atRiskIds = $lowLedger->pluck('student_id')->merge($lowGrade->pluck('student_id'))->unique();
        $students = User::whereIn('id', $atRiskIds)->get(['id', 'name', 'email']);

        $studentsWithReasons = $students->map(function ($student) use ($lowLedger, $lowGrade) {
            $hasLowLedger = $lowLedger->contains('student_id', $student->id);
            $studentLowGrades = $lowGrade->where('student_id', $student->id);
            
            $reasons = [];
            if ($hasLowLedger) {
                $reasons[] = 'Attendance ledger below 150';
            }
            if ($studentLowGrades->isNotEmpty()) {
                $courseNames = $studentLowGrades->map(fn($g) => $g->course->name)->implode(', ');
                $reasons[] = 'Course grade below 60 in ' . $courseNames;
            }

            $student->at_risk_reason = implode(' AND ', $reasons);
            return $student;
        });

        return response()->json($studentsWithReasons);
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

        $earlyWarningStudents = \App\Models\User::where('role', 'student')
            ->whereHas('labGroups', fn($q) => $q->where('cohort_id', $cohort->id))
            ->with(['attendanceLedger' => fn($q) => $q->where('cohort_id', $cohort->id)])
            ->get()
            ->map(function ($student) use ($cohort) {
                $recentSessions = \App\Models\Session::whereHas('engagement', fn($q) => $q->where('cohort_id', $cohort->id))
                    ->where('is_delivered', true)
                    ->orderByDesc('session_date')
                    ->take(3)
                    ->pluck('id');

                $recentAbsences = \App\Models\AttendanceRecord::where('student_id', $student->id)
                    ->whereIn('session_id', $recentSessions)
                    ->where('status', 'absent')
                    ->count();

                $ledger = $student->attendanceLedger->first();
                $trend  = $recentAbsences >= 2 ? 'declining' : 'stable';

                if ($trend === 'declining') {
                    return [
                        'student_id'      => $student->id,
                        'name'            => $student->name,
                        'email'           => $student->email,
                        'ledger_balance'  => $ledger?->balance,
                        'recent_absences' => $recentAbsences,
                        'trend'           => $trend,
                    ];
                }
                return null;
            })->filter()->values();

        return response()->json([
            'cohort_id'          => $cohort->id,
            'grader_consistency' => $graderConsistency,
            'early_warning'      => $earlyWarningStudents,
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
