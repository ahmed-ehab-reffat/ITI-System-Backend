<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\StoreEngagementRequest;
use App\Http\Requests\Engagement\UpdateEngagementRequest;
use App\Http\Resources\EngagementResource;
use App\Models\Cohort;
use App\Models\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EngagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Cohort $cohort): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Engagement::class);

        $perPage = $request->integer('per_page', 15);

        $engagements = $cohort->engagements()
            ->with(['instructor', 'labGroup'])
            ->paginate($perPage);

        return EngagementResource::collection($engagements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEngagementRequest $request, Cohort $cohort): JsonResponse
    {
        $this->authorize('create', Engagement::class);

        $engagement = $cohort->engagements()->create($request->validated());

        return response()->json(
            new EngagementResource($engagement->load(['instructor', 'labGroup'])),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Engagement $engagement): EngagementResource
    {
        $this->authorize('view', $engagement);

        return new EngagementResource($engagement->load(['instructor', 'labGroup']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEngagementRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $engagement->update($request->validated());

        return response()->json(
            new EngagementResource($engagement->load(['instructor', 'labGroup']))
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Engagement $engagement): JsonResponse
    {
        $this->authorize('delete', $engagement);

        $engagement->delete();

        return response()->json(null, 204);
    }
}
