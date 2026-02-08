<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('name', 'Boss User')->first();

if (!$user) {
    echo "User 'Boss User' not found.\n";
    exit;
}

echo "User Found: {$user->name} (ID: {$user->id})\n";

$teams = $user->teams;
echo "Teams Count: " . $teams->count() . "\n";

if ($teams->isEmpty()) {
    echo "User has no teams.\n";
    exit;
}

$teamIds = $teams->pluck('id');
echo "Team IDs: " . $teamIds->implode(', ') . "\n";

$meetings = App\Models\Meeting::whereIn('team_id', $teamIds)->count();
echo "Meetings Count (Total): $meetings\n";

$processedMeetings = App\Models\Meeting::whereIn('team_id', $teamIds)->where('status', 'processed')->count();
echo "Processed Meetings: $processedMeetings\n";

$tasks = App\Models\Task::whereIn('team_id', $teamIds)->count();
echo "Tasks Count (Total): $tasks\n";

$userId = $user->id;
$pendingTasks = App\Models\Task::whereIn('team_id', $teamIds)
    ->where('status', '!=', 'completed')
    ->where(function ($query) use ($userId) {
        $query->whereHas('assignees', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->orWhereDoesntHave('assignees');
    })
    ->count();

echo "Pending Tasks (User/Unassigned): $pendingTasks\n";
