<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\GradeSubmissionRequest;
use App\Http\Requests\Submission\StoreSubmissionRequest;
use App\Http\Resources\SubmissionResource;
use App\Models\Session;
use App\Models\Submission;
use App\Models\User;
use App\Services\LatePenaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function __construct(
        private readonly LatePenaltyService $latePenalty,
    ) {}

    public function index(Request $request, Session $session): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Submission::class, $session]);

        $query = $session->submissions()->with(['student', 'session']);

        if ($request->user()->isInstructor()) {
            $labGroupId = $session->engagement->lab_group_id;

            if ($labGroupId) {
                $query->whereHas(
                    'student.labGroups',
                    fn ($q) => $q->where('lab_groups.id', $labGroupId),
                );
            }
        }

        return SubmissionResource::collection($query->get());
    }

    public function store(StoreSubmissionRequest $request, Session $session): JsonResponse
    {
        $this->authorize('create', [Submission::class, $session]);

        $submittedAt  = now();
        $daysLate     = $this->latePenalty->daysLate($session->session_date, $submittedAt);
        $latePenalty  = $this->latePenalty->calculate($daysLate);
        $filePath     = null;
        $url          = $request->input('url');

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('submissions', 's3');
            $url      = null;
        }

        $submission = $session->submissions()->updateOrCreate(
            ['student_id' => $request->user()->id],
            [
                'url'          => $url,
                'file_path'    => $filePath,
                'submitted_at' => $submittedAt,
                'late_penalty' => $latePenalty,
            ],
        );

        return response()->json(
            new SubmissionResource($submission->load(['student', 'session'])),
            201,
        );
    }

    public function grade(
        GradeSubmissionRequest $request,
        Session $session,
        Submission $submission,
    ): JsonResponse {
        abort_if($submission->session_id !== $session->id, 404);

        $this->authorize('grade', $submission);

        $submission->update([
            'raw_score' => $request->validated('raw_score'),
        ]);

        return response()->json(
            new SubmissionResource($submission->load(['student', 'session'])),
        );
    }

    public function studentIndex(Request $request, User $user): AnonymousResourceCollection
    {
        $this->authorize('viewStudentSubmissions', [Submission::class, $user]);

        $submissions = $user->submissions()
            ->with(['session.engagement', 'student'])
            ->orderByDesc('submitted_at')
            ->get();

        return SubmissionResource::collection($submissions);
    }

    public function file(Session $session, Submission $submission): JsonResponse
    {
        abort_if($submission->session_id !== $session->id, 404);

        $this->authorize('view', $submission);

        if (!$submission->file_path) {
            return response()->json(['message' => 'No file found.'], 404);
        }

        $url = Storage::disk('s3')->temporaryUrl(
            $submission->file_path,
            now()->addMinutes(5)
        );

        return response()->json(['url' => $url]);
    }
}
