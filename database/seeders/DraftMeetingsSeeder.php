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

        // Draft 1: Mobile App Kickoff (Detailed)
        $transcript1 = "Speaker 1: Welcome everyone to the mobile app kickoff.\n" .
                       "Speaker 2: I think we should use React Native so we can reuse our web code.\n" .
                       "Speaker 1: Good point. We need to create a new repository for the mobile repo.\n" .
                       "Speaker 3: I will set up the repo and the CI/CD pipeline by tomorrow.\n" .
                       "Speaker 1: Also, we need to design the login screen to match the web app.";

        Meeting::create([
            'team_id' => $team->id,
            'title' => 'Project Kickoff: MeetFlow Mobile App',
            'transcript' => $transcript1, 
            'meeting_date' => now()->addDays(2),
            'status' => 'draft',
            'created_by' => $user->id,
            'participants' => json_encode(['Alice', 'Bob', 'Charlie']),
        ]);

        // Draft 2: Detailed Transcript about MeetFlow AI Project (Backend/Frontend)
        $transcript2 = "Speaker 1: Let's review the progress on the MeetFlow AI dashboard.\n" .
                      "Speaker 2: The backend API for task extraction is ready, but we are facing some issues with the database migrations.\n" .
                      "Speaker 1: Okay, priority one is to fix the migration for the 'status' column. It needs to include 'draft'.\n" .
                      "Speaker 2: I'll handle the migration fix by end of day today.\n" .
                      "Speaker 1: Great. Also, we need to update the frontend MeetingDetailView to display the extracted tasks properly.\n" .
                      "Speaker 3: I can take that. I'll ensure the UI shows the tasks in a list format.\n" .
                      "Speaker 1: Perfect. Finally, please prepare a demo video for the stakeholders by Friday.";

        Meeting::create([
            'team_id' => $team->id,
            'title' => 'MeetFlow AI Weekly Sync',
            'transcript' => $transcript2,
            'meeting_date' => now()->subDays(1),
            'status' => 'draft',
            'created_by' => $user->id,
            'participants' => json_encode(['Alice', 'Bob', 'Charlie']),
        ]);

        // Draft 3: Marketing Strategy (Detailed)
        $transcript3 = "Speaker 1: How are we launching this product?\n" .
                       "Speaker 2: We are planning a Product Hunt launch next month.\n" .
                       "Speaker 1: We need to prepare the graphic assets for the Product Hunt page.\n" .
                       "Speaker 3: I can design the banners and the logo animations.\n" .
                       "Speaker 1: Excellent. Also, we should write a blog post about how MeetFlow AI saves time.\n" .
                       "Speaker 2: I'll draft the blog post and share it with the team for review.";

        Meeting::create([
            'team_id' => $team->id,
            'title' => 'Quick Sync: Marketing Strategy',
            'transcript' => $transcript3,
            'meeting_date' => now(),
            'status' => 'draft',
            'created_by' => $user->id,
            'participants' => json_encode(['Alice', 'Bob']),
        ]);

        $this->command->info('Draft meetings seeded successfully!');
    }
}
