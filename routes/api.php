<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\KanbanController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\RiskAlertController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- DEBUG ROUTES ---
Route::get('/debug-config', function () {
    return response()->json([
        'config_gemini' => config('gemini'),
        'env_model' => env('GEMINI_MODEL'),
    ]);
});

Route::post('/test-ai', function (Illuminate\Http\Request $request) {
    try {
        $ai = new \App\Services\AIService();
        // Use reflection to access protected callGeminiAPI for raw test
        $reflection = new ReflectionClass($ai);
        $method = $reflection->getMethod('callGeminiAPI');
        $method->setAccessible(true);
        
        $prompt = $request->input('prompt', 'Hello, are you working?');
        $response = $method->invoke($ai, $prompt);
        
        return response()->json([
            'status' => 'success',
            'response' => $response
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/debug-models', function () {
    $apiKey = config('gemini.api_key');
    $baseUrl = config('gemini.base_url'); // e.g. https://generativelanguage.googleapis.com/v1beta
    
    // The endpoint to list models is GET /models
    // If base_url ends in v1beta, we want .../v1beta/models
    
    $url = "{$baseUrl}/models?key={$apiKey}";
    
    $response = Illuminate\Support\Facades\Http::get($url);
    
    return response()->json([
        'url' => $url,
        'status' => $response->status(),
        'body' => $response->json(),
    ]);
});
// --------------------

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Teams
    Route::apiResource('teams', TeamController::class);
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember']);

    // Meetings
    Route::apiResource('meetings', MeetingController::class);
    Route::post('/meetings/{meeting}/process', [MeetingController::class, 'process']);
    Route::post('/meetings/{meeting}/summary', [MeetingController::class, 'generateSummary']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::post('/tasks/{task}/move', [TaskController::class, 'move']);
    Route::post('/tasks/{task}/whatsapp', [TaskController::class, 'generateWhatsAppMessage']);
    Route::post('/tasks/{task}/analyze-risk', [TaskController::class, 'analyzeRisk']);
    Route::post('/tasks/{task}/email', [TaskController::class, 'generateFollowUpEmail']);
    Route::get('/tasks/productivity', [TaskController::class, 'getProductivityScore']);

    // Kanban
    Route::get('/kanban/boards', [KanbanController::class, 'index']);
    Route::post('/kanban/boards', [KanbanController::class, 'store']);
    Route::get('/kanban/boards/{kanbanBoard}', [KanbanController::class, 'show']);
    Route::put('/kanban/boards/{kanbanBoard}/columns', [KanbanController::class, 'updateColumns']);

    // Follow-up messages
    Route::get('/follow-ups', [FollowUpController::class, 'index']);
    Route::get('/follow-ups/{followUpMessage}', [FollowUpController::class, 'show']);
    Route::post('/follow-ups/{followUpMessage}/send', [FollowUpController::class, 'send']);

    // Risk alerts
    Route::get('/alerts', [RiskAlertController::class, 'index']);
    Route::get('/alerts/{riskAlert}', [RiskAlertController::class, 'show']);
    Route::put('/alerts/{riskAlert}/resolve', [RiskAlertController::class, 'resolve']);

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [\App\Http\Controllers\Api\DashboardController::class, 'getStats']);
        Route::get('/recent-activity', [\App\Http\Controllers\Api\DashboardController::class, 'getRecentActivity']);
        Route::get('/upcoming-deadlines', [\App\Http\Controllers\Api\DashboardController::class, 'getUpcomingDeadlines']);
    });
});
