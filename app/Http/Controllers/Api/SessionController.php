<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Session\StoreSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Engagement;
use App\Models\Session;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SessionController extends Controller
{
    public function __construct(private readonly BillingService $billingService) {}

  
     // List all sessions for an engagement.
     // Track admins see all; instructors see only their own engagement's sessions.
     
    public function index(Engagement $engagement): AnonymousResourceCollection
    {
       $this->authorize('viewAny', [Session::class, $engagement]);

        $sessions = $engagement->sessions()->orderBy('session_date')->get();

        return SessionResource::collection($sessions);
    }

     // Track admin creates a session inside an engagement.
    public function store(StoreSessionRequest $request, Engagement $engagement): SessionResource
    {
        $this->authorize('create', [Session::class, $engagement]);

        $session = $engagement->sessions()->create($request->validated());

        return new SessionResource($session);
    }

     // Show a single session (shallow route).
    public function show(Session $session): SessionResource
    {
        $this->authorize('view', $session);

        return new SessionResource($session->load('engagement'));
    }

     // Track admin may delete an undelivered session.
    
    public function destroy(Session $session): JsonResponse
    {
      //  $this->authorize('delete', $session);

        abort_if($session->is_delivered, 422, __('messages.cannot_delete_delivered'));

        $session->delete();

        return response()->json(['message' => __('messages.session_deleted')]);
    }

   
     // Mark session as delivered; triggers billing calculation.
     
    public function deliver(Session $session): SessionResource
    {
       $this->authorize('deliver', $session);

        abort_if($session->is_delivered, 422, __('messages.session_already_delivered'));

        $session->update(['is_delivered' => true]);

        // Notify billing — BillingService stub will be filled in later
        $this->billingService->recordForSession($session);

        return new SessionResource($session->fresh('engagement'));
    }
}

   
