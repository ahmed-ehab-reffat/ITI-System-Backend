<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Announcement\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Cohort;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    // GET /cohorts/{cohort}/announcements
    public function index(Cohort $cohort)
    {
        $announcements = $cohort->announcements()->with('author')->latest()->get();
        return AnnouncementResource::collection($announcements);
    }

    // POST /cohorts/{cohort}/announcements
    public function store(StoreAnnouncementRequest $request, Cohort $cohort)
    {
        $this->authorize('create', Announcement::class);

        $announcement = $cohort->announcements()->create([
            'author_id' => auth()->id(),
            'title'     => $request->title,
            'body'      => $request->body,
        ]);

        return new AnnouncementResource($announcement->load('author'));
    }

    // GET /announcements/{announcement}
    public function show(Announcement $announcement)
    {
        return new AnnouncementResource($announcement->load('author', 'cohort'));
    }

    // PUT /announcements/{announcement}
    public function update(StoreAnnouncementRequest $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $announcement->update($request->only('title', 'body'));
        return new AnnouncementResource($announcement);
    }

    // DELETE /announcements/{announcement}
    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('delete', $announcement);
        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted.']);
    }
}
