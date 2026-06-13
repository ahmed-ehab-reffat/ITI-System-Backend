<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Session;
use App\Services\QrService;
use Illuminate\Http\Request;

class QrController extends Controller
{
    public function __construct(private readonly QrService $qr) {}

    // GET /qr/session/{session}
    public function generate(Session $session)
    {
        $payload = $this->qr->generatePayload($session->id);
        return response()->json(['qr_payload' => $payload]);
    }

    // POST /qr/scan
    public function scan(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'payload'    => 'required|string',
        ]);

        if (!$this->qr->verifyPayload($request->payload, $request->session_id)) {
            return response()->json(['message' => 'Invalid or expired QR code.'], 422);
        }

        $studentId = auth()->id();
        $record = AttendanceRecord::where('session_id', $request->session_id)
            ->where('student_id', $studentId)
            ->first();

        if (!$record) {
            $record = AttendanceRecord::create([
                'session_id' => $request->session_id,
                'student_id' => $studentId,
                'status'     => 'present',
                'arrived_at' => now(),
            ]);
            return response()->json(['status' => 'checked_in', 'arrived_at' => $record->arrived_at]);
        }

        if ($record->arrived_at && !$record->left_at) {
            $record->update(['left_at' => now()]);
            return response()->json(['status' => 'checked_out', 'left_at' => $record->left_at]);
        }

        return response()->json(['message' => 'Already checked out.'], 409);
    }
}
