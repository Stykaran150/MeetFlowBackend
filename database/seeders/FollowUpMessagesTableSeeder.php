<?php

namespace Database\Seeders;

use App\Models\FollowUpMessage;
use App\Models\Meeting;
use Illuminate\Database\Seeder;

class FollowUpMessagesTableSeeder extends Seeder
{
    public function run(): void
    {
        $meeting = Meeting::where('title', 'Q1 Product Roadmap Review')->first();

        if (!$meeting) return;

        FollowUpMessage::create([
            'meeting_id' => $meeting->id,
            'subject' => 'Follow-up: Q1 Roadmap Next Steps',
            'body' => "Hi Team,\n\nHere is a summary of our meeting:\n- AI Training is ongoing.\n- Dashboard implementation is top priority.\n\nAction Items:\n- Mike: Finish data scraping.\n- Alex: Start dashboard coding.\n\nBest,\nAlex",
            'recipients' => ['team@meetflow.ai'],
            'status' => 'draft',
        ]);
    }
}
