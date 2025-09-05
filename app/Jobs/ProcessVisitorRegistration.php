<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Visitor;
use App\Models\VisitHistory;
use Illuminate\Support\Facades\Log;

class ProcessVisitorRegistration implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $visitorData;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($visitorData, $userId)
    {
        $this->visitorData = $visitorData;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Process visitor registration in background
            Log::info('Processing visitor registration in background', [
                'visitor_data' => $this->visitorData,
                'user_id' => $this->userId
            ]);

            // Here you can add any heavy operations like:
            // - Sending welcome emails
            // - Updating analytics
            // - Notifying staff members
            // - Generating reports
            // - Syncing with external systems

            // For now, we'll just log the operation
            Log::info('Visitor registration processed successfully', [
                'visitor_data' => $this->visitorData
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing visitor registration', [
                'error' => $e->getMessage(),
                'visitor_data' => $this->visitorData
            ]);
        }
    }
}
