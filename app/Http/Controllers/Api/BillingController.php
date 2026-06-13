<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillingRecordResource;
use App\Models\BillingRecord;
use App\Models\User;
use App\Services\BillingService;

class BillingController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    // GET /billing  — branch_manager only
    public function index()
    {
        abort_unless(auth()->user()->isBranchManager(), 403);

        $instructors = User::whereIn('role', ['instructor'])
            ->with('billingRecords')
            ->get()
            ->map(fn($u) => $this->billing->rollupForUser($u));

        return response()->json($instructors);
    }

    // GET /billing/{user}  — session-by-session breakdown for one person
    public function show(User $user)
    {
        abort_unless(auth()->user()->isBranchManager(), 403);

        $records = BillingRecord::where('user_id', $user->id)
            ->with('session.engagement')
            ->get();

        return BillingRecordResource::collection($records);
    }
}
