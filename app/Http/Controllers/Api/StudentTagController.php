<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Resources\StudentTagResource;
use App\Models\StudentTag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentTagController extends Controller
{
    // GET /students/{user}/tags
    public function index(User $student): AnonymousResourceCollection
    {
        $this->authorize('viewAny', StudentTag::class);

        $tags = $student->tags()->with('tagger')->get();
        return StudentTagResource::collection($tags);
    }

    // POST /students/{user}/tags
    public function store(StoreTagRequest $request, User $student): StudentTagResource
    {
        $this->authorize('create', StudentTag::class);

        // ACC-5: Instructor can only tag their own lab group's students
        if (auth()->user()->isInstructor()) {
            $instructorGroupStudentIds = auth()->user()
                ->engagements()
                ->with('labGroup.students')
                ->get()
                ->flatMap(fn($e) => $e->labGroup?->students->pluck('id') ?? []);

            abort_unless($instructorGroupStudentIds->contains($student->id), 403,
                'You can only tag students in your lab group.');
        }

        $tag = $student->tags()->create([
            'tagged_by'  => auth()->id(),
            'cohort_id'  => $request->cohort_id,
            'tag_type'   => $request->tag_type,
            'tag_value'  => $request->tag_value,
            'note'       => $request->note,
        ]);

        return new StudentTagResource($tag);
    }

    // DELETE /students/{user}/tags/{tag}
    public function destroy(User $student, StudentTag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);
        $tag->delete();
        return response()->json(['message' => 'Tag removed.']);
    }
}
