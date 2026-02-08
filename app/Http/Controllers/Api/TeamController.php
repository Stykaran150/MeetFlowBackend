<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $teams = $request->user()->teams()->with('owner')->get();

        return $this->successResponse($teams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $team = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => $request->user()->id,
        ]);

        // Add owner as team member
        $team->users()->attach($request->user()->id, ['role' => 'owner']);

        return $this->successResponse($team->load('owner'), 'Team created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Team $team): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($team->id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($team->load(['owner', 'users', 'meetings', 'kanbanBoards']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        // Check if user is owner or admin
        $userTeam = $request->user()->teams()->where('teams.id', $team->id)->first();
        if (!$userTeam || !in_array($userTeam->pivot->role, ['owner', 'admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $team->update($request->only('name', 'description'));

        return $this->successResponse($team->fresh());
    }

    /**
     * Add a member to the team by email.
     */
    public function addMember(Request $request, Team $team): JsonResponse
    {
        // Check if authenticated user is owner or admin
        $authUserTeam = $request->user()->teams()->where('teams.id', $team->id)->first();
        if (!$authUserTeam || !in_array($authUserTeam->pivot->role, ['owner', 'admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'role' => 'in:member,admin',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $userToAdd = \App\Models\User::where('email', $request->email)->first();

        // Check if already a member
        if ($team->users()->where('users.id', $userToAdd->id)->exists()) {
            return $this->errorResponse('User is already a member of this team.', 409);
        }

        $team->users()->attach($userToAdd->id, ['role' => $request->role ?? 'member']);

        return $this->successResponse(
            $team->load(['owner', 'users', 'meetings', 'kanbanBoards']), // Reload team data
            'Member added successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Team $team): JsonResponse
    {
        // Only owner can delete
        if ($team->owner_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $team->delete();

        return $this->successResponse(null, 'Team deleted successfully');
    }
}
