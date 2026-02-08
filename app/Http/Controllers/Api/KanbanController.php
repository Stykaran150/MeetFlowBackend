<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KanbanBoard;
use App\Models\Team;
use App\Services\KanbanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class KanbanController extends Controller
{
    protected KanbanService $kanbanService;

    public function __construct(KanbanService $kanbanService)
    {
        $this->kanbanService = $kanbanService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $teamId = $request->query('team_id');
        
        $query = KanbanBoard::with('columns');
        
        if ($teamId) {
            $query->where('team_id', $teamId);
        } else {
            $teamIds = $request->user()->teams->pluck('id');
            $query->whereIn('team_id', $teamIds);
        }

        $boards = $query->get();

        return $this->successResponse($boards);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'sometimes|boolean',
            'columns' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Check if user is member of team
        if (!$request->user()->teams->contains($request->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $team = Team::findOrFail($request->team_id);
        
        $board = $this->kanbanService->createBoard($team, [
            'name' => $request->name,
            'description' => $request->description ?? null,
            'is_default' => $request->is_default ?? false,
            'created_by' => $request->user()->id,
            'columns' => $request->columns ?? null,
        ]);

        return $this->successResponse($board, 'Kanban board created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, KanbanBoard $kanbanBoard): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($kanbanBoard->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $board = $this->kanbanService->getBoardWithTasks($kanbanBoard);

        return $this->successResponse($board);
    }

    /**
     * Update column positions.
     */
    public function updateColumns(Request $request, KanbanBoard $kanbanBoard): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($kanbanBoard->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validator = Validator::make($request->all(), [
            'columns' => 'required|array',
            'columns.*' => 'required|exists:kanban_columns,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $this->kanbanService->updateColumnPositions($kanbanBoard, $request->columns);

        return $this->successResponse([
            'board' => $kanbanBoard->fresh()->load('columns'),
        ], 'Column positions updated successfully');
    }
}
