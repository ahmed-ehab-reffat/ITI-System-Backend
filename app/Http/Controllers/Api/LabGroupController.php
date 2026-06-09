<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabGroup\AssignStudentsRequest;
use App\Http\Requests\LabGroup\StoreLabGroupRequest;
use App\Http\Requests\LabGroup\UpdateLabGroupRequest;
use App\Http\Resources\LabGroupResource;
use App\Models\AttendanceLedger;
use App\Models\Cohort;
use App\Models\LabGroup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LabGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Cohort $cohort): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LabGroup::class);

        $perPage = $request->integer('per_page', 15);

        $labGroups = $cohort->labGroups()->with('students')->paginate($perPage);

        return LabGroupResource::collection($labGroups);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLabGroupRequest $request, Cohort $cohort): JsonResponse
    {
        $this->authorize('create', LabGroup::class);

        $labGroup = $cohort->labGroups()->create($request->validated());

        return response()->json(new LabGroupResource($labGroup->load('students')), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LabGroup $labGroup): LabGroupResource
    {
        $this->authorize('view', $labGroup);

        return new LabGroupResource($labGroup->load('students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLabGroupRequest $request, LabGroup $labGroup): JsonResponse
    {
        $this->authorize('update', $labGroup);

        $labGroup->update($request->validated());

        return response()->json(new LabGroupResource($labGroup->load('students')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabGroup $labGroup): JsonResponse
    {
        $this->authorize('delete', $labGroup);

        $labGroup->delete();

        return response()->json(null, 204);
    }

    /**
     * Assign students to a lab group.
     * ATT-4: create an attendance ledger for each newly assigned student.
     */
    public function assignStudents(AssignStudentsRequest $request, LabGroup $labGroup): JsonResponse
    {
        $this->authorize('update', $labGroup);

        $studentIds = $request->input('student_ids');

        $labGroup->students()->syncWithoutDetaching($studentIds);

        // ATT-4: ensure each assigned student has an attendance ledger for this cohort
        foreach ($studentIds as $studentId) {
            AttendanceLedger::firstOrCreate(
                [
                    'student_id' => $studentId,
                    'cohort_id'  => $labGroup->cohort_id,
                ],
            );
        }

        return response()->json(new LabGroupResource($labGroup->load('students')));
    }

    /**
     * Remove a student from a lab group.
     */
    public function removeStudent(LabGroup $labGroup, User $user): JsonResponse
    {
        $this->authorize('update', $labGroup);

        $labGroup->students()->detach($user->id);

        return response()->json(new LabGroupResource($labGroup->load('students')));
    }
}
