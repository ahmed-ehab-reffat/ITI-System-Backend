<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CohortController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCohortRequest $request): JsonResponse
    {
        $this->authorize('create', Cohort::class);

        // LC-1: one active cohort per track
        $alreadyActive = Cohort::where('track_id', $request->track_id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyActive) {
            return response()->json([
                'message' => 'This track already has an active cohort.',
            ], 409);
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
