<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;

class CleanupMeetingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete meetings with null or empty transcript
        $count = Meeting::whereNull('transcript')
            ->orWhere('transcript', '')
            ->orWhere('transcript', 'like', '%Speaker 1: ...%') // Remove generic placeholders if any
            ->delete();

        $this->command->info("Deleted {$count} meetings with empty or invalid transcripts.");
    }
}
