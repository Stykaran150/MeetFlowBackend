<?php

namespace App\Jobs;

use App\Models\Meeting;
use App\Services\MeetingProcessorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMeetingTranscriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Meeting $meeting
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(MeetingProcessorService $processorService): void
    {
        try {
            $processorService->processMeeting($this->meeting);
        } catch (\Exception $e) {
            Log::error('Failed to process meeting transcript job', [
                'meeting_id' => $this->meeting->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->meeting->update([
                'status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
