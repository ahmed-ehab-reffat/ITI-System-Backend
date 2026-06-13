<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cohort\StoreCohortRequest;
use App\Http\Requests\Cohort\UpdateCohortRequest;
use App\Http\Resources\CohortResource;
use App\Models\Cohort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CohortController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Cohort::class);

        $user = $request->user();
        $perPage = $request->integer('per_page', 15);

        if ($user->isBranchManager()) {
            $cohorts = Cohort::with(['track', 'trackAdmins'])->paginate($perPage);
        } else {
            // track_admin (since viewAny only allows branch_manager and track_admin)
            $cohorts = $user->managedCohorts()->with(['track', 'trackAdmins'])->paginate($perPage);
        }

        return CohortResource::collection($cohorts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCohortRequest $request): JsonResponse
    {
        $this->authorize('create', Cohort::class);

        // LC-1: one active cohort per track
        $status = $request->input('status', 'open');
        if ($status === 'active') {
            $alreadyActive = Cohort::where('track_id', $request->track_id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyActive) {
                return response()->json([
                    'message' => 'This track already has an active cohort.',
                ], 409);
            }
        }

        $cohort = Cohort::create($request->safe()->except('track_admin_ids'));

        if ($request->track_admin_ids) {
            $cohort->trackAdmins()->attach($request->track_admin_ids);
        }

        return response()->json(new CohortResource($cohort->load('trackAdmins', 'track')), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cohort $cohort): CohortResource
    {
        $this->authorize('view', $cohort);

        return new CohortResource($cohort->load(['track', 'trackAdmins']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCohortRequest $request, Cohort $cohort): JsonResponse
    {
        $this->authorize('update', $cohort);

        $trackId = $request->input('track_id', $cohort->track_id);
        $status = $request->input('status', $cohort->status);

        if ($status === 'active') {
            $alreadyActive = Cohort::where('track_id', $trackId)
                ->where('status', 'active')
                ->where('id', '!=', $cohort->id)
                ->exists();

            if ($alreadyActive) {
                return response()->json([
                    'message' => 'This track already has an active cohort.',
                ], 409);
            }
        }

        $cohort->update($request->safe()->except('track_admin_ids'));

        if ($request->has('track_admin_ids')) {
            $cohort->trackAdmins()->sync($request->track_admin_ids);
        }

        return response()->json(new CohortResource($cohort->load(['track', 'trackAdmins'])));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cohort $cohort): JsonResponse
    {
        $this->authorize('delete', $cohort);

        $cohort->delete();

        return response()->json(null, 204);
    }
}
