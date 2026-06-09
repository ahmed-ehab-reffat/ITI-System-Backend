<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Cohort;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Cohort $cohort): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Course::class);

        $perPage = $request->integer('per_page', 15);

        $courses = $cohort->courses()->with('cohort')->paginate($perPage);

        return CourseResource::collection($courses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request, Cohort $cohort): JsonResponse
    {
        $this->authorize('create', Course::class);

        $course = $cohort->courses()->create($request->validated());

        return response()->json(new CourseResource($course->load('cohort')), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): CourseResource
    {
        $this->authorize('view', $course);

        return new CourseResource($course->load('cohort'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $course->update($request->validated());

        return response()->json(new CourseResource($course->load('cohort')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->json(null, 204);
    }
}
