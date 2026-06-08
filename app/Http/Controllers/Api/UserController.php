<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->boolean('expired'), fn ($q) => $q->where('expires_at', '<', now()))
            ->get();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = User::create([
            ...$request->validated(),
            'password' => bcrypt('Welcome@ITI1'),
        ]);

        return response()->json(new UserResource($user), 201);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->update(['expires_at' => now()]);

        return response()->json(['message' => 'Account deactivated.']);
    }
}
