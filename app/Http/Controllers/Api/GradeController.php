<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grade\StoreGradeRequest;
use App\Http\Resources\CourseGradeResource;
use App\Models\AttendanceLedger;
use App\Models\Course;
use App\Models\CourseGrade;
use App\Models\User;
use App\Services\GradeComputationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GradeController extends Controller
{
    public function __construct(
        private readonly GradeComputationService $gradeComputation,
    ) {}

    public function index(Request $request, Course $course): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [CourseGrade::class, $course]);

        $perPage = $request->integer('per_page', 15);

        $grades = $course->grades()
            ->with(['student', 'course'])
            ->paginate($perPage);

        return CourseGradeResource::collection($grades);
    }

    public function store(StoreGradeRequest $request, Course $course): JsonResponse
    {
        $this->authorize('create', CourseGrade::class);

        $validated     = $request->validated();
        $computedScore = $this->gradeComputation->normalizeExamScore(
            (float) $validated['exam_raw_score'],
            (float) $validated['exam_raw_max'],
            (float) $course->exam_weight,
        );

        $grade = $course->grades()->updateOrCreate(
            ['student_id' => $validated['student_id']],
            [
                'exam_raw_score' => $validated['exam_raw_score'],
                'exam_raw_max'   => $validated['exam_raw_max'],
                'computed_score' => $computedScore,
            ],
        );

        return response()->json(
            new CourseGradeResource($grade->load(['student', 'course'])),
            201,
        );
    }

    public function show(Course $course, CourseGrade $grade): CourseGradeResource
    {
        abort_if($grade->course_id !== $course->id, 404);

        $this->authorize('view', $grade);

        return new CourseGradeResource($grade->load(['student', 'course']));
    }

    public function summary(User $user): JsonResponse
    {
        $this->authorize('viewSummary', [CourseGrade::class, $user]);

        $ledgers = AttendanceLedger::where('student_id', $user->id)
            ->with('cohort')
            ->get();

        $cohortIds = $ledgers->pluck('cohort_id');

        $courses = Course::whereIn('cohort_id', $cohortIds)
            ->with(['grades' => fn ($q) => $q->where('student_id', $user->id)])
            ->get();

        $courseScores = $courses->map(function (Course $course) use ($user) {
            $grade        = $course->grades->first();
            $examComputed = $grade?->computed_score;
            $labScore     = $this->gradeComputation->computeLabScore($course, $user->id);
            $courseTotal  = $this->gradeComputation->courseTotal($course, $user->id, $examComputed);

            return [
                'course_id'    => $course->id,
                'course_name'  => $course->name,
                'lab_score'    => $labScore,
                'exam_score'   => $examComputed,
                'course_total' => $courseTotal,
            ];
        });

        $ledgerBalance = $ledgers->sum('balance');
        $grandTotal    = round($ledgerBalance + $courseScores->sum('course_total'), 2);

        return response()->json([
            'student' => [
                'id'   => $user->id,
                'name' => $user->name,
            ],
            'ledgers' => $ledgers->map(fn ($ledger) => [
                'cohort_id'   => $ledger->cohort_id,
                'cohort_name' => $ledger->cohort?->name,
                'balance'     => $ledger->balance,
            ]),
            'courses'      => $courseScores,
            'grand_total'  => $grandTotal,
        ]);
    }
}
