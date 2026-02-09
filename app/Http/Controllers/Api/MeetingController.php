<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\MeetingProcessorService;
use App\Services\AIService;
use App\Jobs\ProcessMeetingTranscriptJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    protected MeetingProcessorService $processorService;
    protected AIService $aiService;

    public function __construct(MeetingProcessorService $processorService, AIService $aiService)
    {
        $this->processorService = $processorService;
        $this->aiService = $aiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $teamId = $request->query('team_id');
        
        $query = Meeting::with(['team', 'creator', 'tasks']);
        
        if ($teamId) {
            // Security: Ensure user belongs to this team
            if (!$request->user()->teams->contains($teamId)) {
                return $this->errorResponse('Unauthorized', 403);
            }
            $query->where('team_id', $teamId);
        } else {
            // Only show meetings from user's teams
            $teamIds = $request->user()->teams->pluck('id')->toArray();
            $query->whereIn('team_id', $teamIds);
        }

        $meetings = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse($meetings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Transcript is required ONLY if audio_file is missing.
            'transcript' => 'required_without:audio_file|nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a,ogg|max:20480', // Max 20MB
            'participants' => 'nullable|array',
            'meeting_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Check if user is member of team
        if (!$request->user()->teams->contains($request->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $transcript = $request->transcript;

        // Handle Audio Upload & Transcription
        if ($request->hasFile('audio_file')) {
            try {
                $file = $request->file('audio_file');
                $path = $file->getRealPath();
                $mimeType = $file->getMimeType();

                // Call AI Service to transcribe
                // Note: In production, this should likely be a job. For hackathon MVP, we do it inline or sync.
                // Assuming inline for simplicity as per user request flow "generate task when user select audio"
                $transcript = $this->aiService->transcribeAudio($path, $mimeType);

            } catch (\Exception $e) {
                return $this->errorResponse('Audio Transcription Failed: ' . $e->getMessage(), 500);
            }
        }

        $meeting = Meeting::create([
            'team_id' => $request->team_id,
            'title' => $request->title,
            'description' => $request->description ?? null,
            'transcript' => $transcript, // Use the provided or generated transcript
            'participants' => $request->participants ?? [],
            'meeting_date' => $request->meeting_date ?? null,
            'status' => $request->status === 'draft' ? 'draft' : 'pending',
            'created_by' => $request->user()->id,
        ]);

        return $this->successResponse($meeting->load(['team', 'creator']), 'Meeting created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Meeting $meeting): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($meeting->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($meeting->load([
            'team',
            'creator',
            'tasks.assignees',
            'tasks.kanbanColumn',
            'followUpMessages',
        ]));
    }

    /**
     * Process meeting transcript.
     */
    public function process(Request $request, Meeting $meeting): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($meeting->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($meeting->status === 'processing') {
            return $this->errorResponse('Meeting is already being processed', 400);
        }
        
        // Allow re-processing for now
        // if ($meeting->status === 'processed') {
        //     return $this->errorResponse('Meeting has already been processed', 400);
        // }

        try {
            // Dispatch job for async processing
            ProcessMeetingTranscriptJob::dispatch($meeting);
            
            return $this->successResponse([
                'meeting' => $meeting->fresh(),
            ], 'Meeting processing started');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to start meeting processing: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Meeting $meeting): JsonResponse
    {
        // Check if user is creator or team admin
        $userTeam = $request->user()->teams()->where('teams.id', $meeting->team_id)->first();
        $canDelete = $meeting->created_by === $request->user()->id 
            || ($userTeam && in_array($userTeam->pivot->role, ['owner', 'admin']));

        if (!$canDelete) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $meeting->delete();

        return $this->successResponse(null, 'Meeting deleted successfully');
    }

    /**
     * Generate executive summary for meeting.
     */
    public function generateSummary(Request $request, Meeting $meeting): JsonResponse
    {
        // Check if user is member of team
        if (!$request->user()->teams->contains($meeting->team_id)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $tasks = $meeting->tasks()->get()->map(function ($task) {
            return [
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'deadline' => $task->deadline?->format('Y-m-d'),
                'owner' => $task->assignees->first()?->name ?? 'Unassigned',
            ];
        })->toArray();

        try {
            $summary = $this->aiService->generateExecutiveSummary($tasks, $meeting->title);
            return $this->successResponse($summary, 'Summary generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate summary: ' . $e->getMessage(), 500);
        }
    }
}
