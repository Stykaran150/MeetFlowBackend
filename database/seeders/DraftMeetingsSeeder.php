<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\Team;
use App\Models\User;

class DraftMeetingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Try to find a user and team to attach drafts to
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No user found to assign drafts to. Skipping.');
            return;
        }

        // Get user's first team or create one if needed (though usually we assume one exists)
        $team = $user->teams()->first() ?? Team::first();

        if (!$team) {
            $this->command->warn('No team found for user based on relationship or Team::first(). Skipping.');
            return;
        }

        $this->command->info(" Creating draft meetings for User: {$user->email} and Team: {$team->name}");

        // Draft 1: Empty transcript (New Project Kickoff)
        Meeting::create([
            'team_id' => $team->id,
            'title' => 'Project Kickoff: MeetFlow Mobile App',
            'transcript' => '', 
            'meeting_date' => now()->addDays(2),
            'status' => 'draft',
            'created_by' => $user->id,
            'participants' => json_encode(['Alice', 'Bob', 'Charlie']),
        ]);

        // Draft 2: Detailed Transcript about MeetFlow AI Project
        // Contains clear tasks for the AI to extract
        $transcript = "Speaker 1: Let's review the progress on the MeetFlow AI dashboard.\n" .
                      "Speaker 2: The backend API for task extraction is ready, but we are facing some issues with the database migrations.\n" .
                      "Speaker 1: Okay, priority one is to fix the migration for the 'status' column. It needs to include 'draft'.\n" .
                      "Speaker 2: I'll handle the migration fix by end of day today.\n" .
                      "Speaker 1: Great. Also, we need to update the frontend MeetingDetailView to display the extracted tasks properly.\n" .
                      "Speaker 3: I can take that. I'll ensure the UI shows the tasks in a list format.\n" .
                      "Speaker 1: Perfect. Finally, please prepare a demo video for the stakeholders by Friday.";

        Meeting::create([
            'team_id' => $team->id,
            'title' => 'MeetFlow AI Weekly Sync',
            'transcript' => $transcript,
            'meeting_date' => now()->subDays(1),
            'status' => 'draft',
            'created_by' => $user->id,
            'participants' => json_encode(['Alice', 'Bob', 'Charlie']),
        ]);

        // Draft 3: Just a title
        Meeting::create([
            'team_id' => $team->id,
            'title' => 'Quick Sync: Marketing Strategy',
            'transcript' => '',
            'meeting_date' => now(),
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->command->info('Draft meetings seeded successfully!');
    }
}
