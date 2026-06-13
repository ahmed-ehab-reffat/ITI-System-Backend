<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;
use App\Http\Resources\TrackResource;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tracks = Track::with('activeCohort')->get();
        return TrackResource::collection($tracks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:tracks,code',
        ]);

        $track = Track::create($validated);
        
        return new TrackResource($track);
    }

    /**
     * Display the specified resource.
     */
    public function show(Track $track)
    {
        $track->load('activeCohort');
        return new TrackResource($track);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Track $track)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:tracks,code,' . $track->id,
        ]);

        $track->update($validated);

        return new TrackResource($track);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Track $track)
    {
        $track->delete();
        return response()->noContent();
    }
}
