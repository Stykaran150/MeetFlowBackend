<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class MeetingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::where('name', 'MeetFlow Product Launch')->first();
        $owner = User::where('email', 'demo@meetflow.ai')->first();

        if (!$team || !$owner) return;

        $transcript = "Alex: Okay team, let's discuss the Q1 roadmap. Sarah, what's the status on the AI integration?
Sarah: The AI model is training well, but we need more data for the summarization feature. Mike is handling that.
Mike: Yeah, I'm scraping some dataset now. It should be ready by Friday.
Jenna: What about the UI? I have the designs ready for the dashboard.
Alex: Great. We need to prioritize the dashboard implementation.
Sarah: Also, we need to send follow-up emails to our beta testers.
Alex: Agreed. Let's add that as a task.";

        Meeting::create([
            'team_id' => $team->id,
            'title' => 'Q1 Product Roadmap Review',
            'transcript' => $transcript,
            'summary' => 'The team discussed the Q1 roadmap. The AI model training is in progress but requires more data. Dashboard designs are ready. Priority is given to dashboard implementation and beta tester follow-ups.',
            'created_by' => $owner->id,
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }
}
