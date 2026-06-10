<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcuseRequest\ReviewExcuseRequest;
use App\Http\Requests\ExcuseRequest\StoreExcuseRequest;
use App\Http\Resources\ExcuseRequestResource;
use App\Models\ExcuseRequest;
use App\Services\AttendanceLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ExcuseRequestController extends Controller
{
    public function __construct(
        private readonly AttendanceLedgerService $ledger,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ExcuseRequest::class);

        $user    = $request->user();
        $perPage = $request->integer('per_page', 15);
        $query   = ExcuseRequest::query()->with(['student', 'attendanceRecord', 'reviewer']);

        if ($user->isStudent()) {
            $query->where('student_id', $user->id);
        } elseif ($user->isTrackAdmin()) {
            $cohortIds = $user->managedCohorts()->pluck('cohorts.id');
            $query->whereHas(
                'attendanceRecord.session.engagement',
                fn ($q) => $q->whereIn('cohort_id', $cohortIds),
            );
        }

        return ExcuseRequestResource::collection(
            $query->latest()->paginate($perPage),
        );
    }

    public function store(StoreExcuseRequest $request): JsonResponse
    {
        $this->authorize('create', ExcuseRequest::class);

        $data           = $request->validated();
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('excuse-attachments', 'local');
        }

        $excuse = ExcuseRequest::create([
            'attendance_record_id' => $data['attendance_record_id'],
            'student_id'           => $request->user()->id,
            'reason'               => $data['reason'],
            'attachment_path'      => $attachmentPath,
            'status'               => 'requested',
        ]);

        return response()->json(
            new ExcuseRequestResource($excuse->load(['student', 'attendanceRecord'])),
            201,
        );
    }

    public function show(ExcuseRequest $excuseRequest): ExcuseRequestResource
    {
        $this->authorize('view', $excuseRequest);

        return new ExcuseRequestResource(
            $excuseRequest->load(['student', 'attendanceRecord', 'reviewer']),
        );
    }

    public function approve(ReviewExcuseRequest $request, ExcuseRequest $excuseRequest): JsonResponse
    {
        $this->authorize('approve', $excuseRequest);

        if ($excuseRequest->status !== 'requested') {
            return response()->json([
                'message' => 'This excuse request has already been reviewed.',
            ], 409);
        }

        $excuse = DB::transaction(function () use ($request, $excuseRequest) {
            $record   = $excuseRequest->attendanceRecord()->with(['student', 'session.engagement'])->first();
            $cohortId = $record->session->engagement->cohort_id;
            $credit   = config('attendance.unexcused_deduction') - config('attendance.excused_deduction');

            $excuseRequest->update([
                'status'        => 'approved',
                'reviewed_by'   => $request->user()->id,
                'reviewer_note' => $request->validated('reviewer_note'),
            ]);

            $record->update(['status' => 'excused']);
            $this->ledger->credit($record->student, $cohortId, $credit);

            return $excuseRequest->fresh(['student', 'attendanceRecord', 'reviewer']);
        });

        return response()->json(new ExcuseRequestResource($excuse));
    }

    public function reject(ReviewExcuseRequest $request, ExcuseRequest $excuseRequest): JsonResponse
    {
        $this->authorize('reject', $excuseRequest);

        if ($excuseRequest->status !== 'requested') {
            return response()->json([
                'message' => 'This excuse request has already been reviewed.',
            ], 409);
        }

        $excuseRequest->update([
            'status'        => 'rejected',
            'reviewed_by'   => $request->user()->id,
            'reviewer_note' => $request->validated('reviewer_note'),
        ]);

        return response()->json(
            new ExcuseRequestResource($excuseRequest->load(['student', 'attendanceRecord', 'reviewer'])),
        );
    }
}
