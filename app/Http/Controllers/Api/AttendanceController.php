<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Models\Session;
use App\Models\User;
use App\Services\AttendanceLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceLedgerService $ledger,
    ) {}

     // List all attendance records for a session.
    public function index(Session $session): AnonymousResourceCollection
    {
       $this->authorize('viewAny', [AttendanceRecord::class, $session]);

        $user    = auth()->user();
        $records = $session->attendanceRecords()->with('student');

        // Instructors are scoped to their lab group(s) inside the engagement
        if ($user->role === 'instructor') {
            $labGroupStudentIds = $session->engagement
                ->labGroup
                ?->students()
                ->pluck('users.id') ?? collect();

            $records->whereIn('student_id', $labGroupStudentIds);
        }

        return AttendanceRecordResource::collection($records->get());
    }

    
     // Create attendance records for a session.
     
    public function store(StoreAttendanceRequest $request, Session $session): AnonymousResourceCollection
    {
        $this->authorize('create', [AttendanceRecord::class, $session]);

        $cohortId = $session->engagement->cohort_id;
        $created  = collect();

        foreach ($request->validated('records') as $row) {
            $student = User::findOrFail($row['student_id']);

            $record = $session->attendanceRecords()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'arrived_at' => $row['arrived_at'] ?? null,
                    'left_at'    => $row['left_at']    ?? null,
                    'status'     => $row['status'],
                ],
            ); 
            if ($record->wasRecentlyCreated) {
                $this->ledger->deduct($student, $cohortId, $row['status']);
            }

            $created->push($record->load('student'));
        }

        return AttendanceRecordResource::collection($created);
    }

     // Update an existing record's status 
     
    public function update(
        UpdateAttendanceRequest $request,
        Session $session,
        AttendanceRecord $record,
    ): AttendanceRecordResource {
       $this->authorize('update', $record);

        $oldStatus = $record->status;
        $newData   = $request->validated();

        $record->update($newData);

        if (isset($newData['status']) && $newData['status'] !== $oldStatus) {
            $cohortId = $session->engagement->cohort_id;
            $this->ledger->adjustForStatusChange(
                $record,
                $oldStatus,
                $newData['status'],
                $cohortId,
            );
        }

        return new AttendanceRecordResource($record->load('student'));
    }

     // A student's full attendance history across all sessions
   
    public function studentHistory(User $user): AnonymousResourceCollection
    {
       $this->authorize('viewHistory', [AttendanceRecord::class, $user]);

        $records = AttendanceRecord::with(['session.engagement.cohort'])
            ->where('student_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return AttendanceRecordResource::collection($records);
    }
}