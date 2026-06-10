<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grade\OverrideGradeRequest;
use App\Http\Resources\GradeOverrideResource;
use App\Models\CourseGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class GradeOverrideController extends Controller
{
    public function index(CourseGrade $grade): AnonymousResourceCollection
    {
        $this->authorize('viewOverrides', $grade);

        $overrides = $grade->overrides()
            ->with('overriddenBy')
            ->orderBy('created_at')
            ->get();

        return GradeOverrideResource::collection($overrides);
    }

    public function store(OverrideGradeRequest $request, CourseGrade $grade): JsonResponse
    {
        $this->authorize('override', $grade);

        // GRD-6: capture original value before updating
        $originalValue = $grade->computed_score;

        $override = DB::transaction(function () use ($request, $grade, $originalValue) {
            $override = $grade->overrides()->create([
                'overridden_by'  => $request->user()->id,
                'original_value' => $originalValue,
                'new_value'      => $request->validated('new_value'),
                'reason'         => $request->validated('reason'),
            ]);

            $grade->update(['computed_score' => $request->validated('new_value')]);

            return $override;
        });

        return response()->json(
            new GradeOverrideResource($override->load('overriddenBy')),
            201,
        );
    }
}
