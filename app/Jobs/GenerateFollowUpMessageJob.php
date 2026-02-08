<?php

namespace App\Jobs;

use App\Models\FollowUpMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateFollowUpMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public FollowUpMessage $followUpMessage
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // TODO: Implement actual email sending
            // For now, just mark as sent
            $this->followUpMessage->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Log::info('Follow-up message sent', [
                'follow_up_message_id' => $this->followUpMessage->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send follow-up message', [
                'follow_up_message_id' => $this->followUpMessage->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->followUpMessage->update([
                'status' => 'failed',
            ]);
            
            throw $e;
        }
    }
}
