<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceLedger;
use App\Models\AttendanceRecord;
use App\Models\Session;
use App\Models\User;
use App\Services\AttendanceLedgerService;
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

        $records = $session->attendanceRecords()
            ->with(['student', 'session.engagement'])
            ->get()
            ->keyBy('student_id');
        $students = $this->sessionStudents($session);

        $sessionRows = $students->map(function (User $student) use ($session, $records) {
            if ($records->has($student->id)) {
                return $records->get($student->id);
            }

            $record = new AttendanceRecord([
                'session_id' => $session->id,
                'student_id' => $student->id,
                'status' => null,
                'arrived_at' => null,
                'left_at' => null,
            ]);

            $record->setRelation('student', $student);
            $record->setRelation('session', $session);

            return $record;
        });

        return AttendanceRecordResource::collection($sessionRows);
    }

    private function sessionStudents(Session $session)
    {
        if ($session->engagement->labGroup) {
            return $session->engagement->labGroup->students()->get();
        }

        return AttendanceLedger::where('cohort_id', $session->engagement->cohort_id)
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter();
    }

    // Create attendance records for a session.

    public function store(StoreAttendanceRequest $request, Session $session): AnonymousResourceCollection
    {
        $this->authorize('create', [AttendanceRecord::class, $session]);

        $cohortId = $session->engagement->cohort_id;
        $created = collect();

        foreach ($request->validated('records') as $row) {
            $student = User::findOrFail($row['student_id']);

            $record = $session->attendanceRecords()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'arrived_at' => $row['arrived_at'] ?? null,
                    'left_at' => $row['left_at'] ?? null,
                    'status' => $row['status'],
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
        $newData = $request->validated();

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
