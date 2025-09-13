<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InteractionHistory;
use App\Models\Remark;
use Illuminate\Support\Facades\DB;

class FixCompletionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:completion-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix interactions that have remarks but are not marked as completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Starting database cleanup for completion status...');
        $this->newLine();

        try {
            // Get all interactions that have remarks but are not marked as completed
            $interactionsToFix = DB::table('interaction_history')
                ->whereIn('interaction_id', function($query) {
                    $query->select('interaction_id')
                          ->from('remarks')
                          ->whereNotNull('interaction_id');
                })
                ->where('is_completed', false)
                ->get();

            $this->info("📊 Found {$interactionsToFix->count()} interactions that need to be marked as completed.");
            $this->newLine();

            if ($interactionsToFix->count() > 0) {
                $this->info('🔍 Details of interactions to fix:');
                $this->line(str_repeat('-', 80));
                $this->line(sprintf('%-15s %-20s %-15s %-20s', 'Interaction ID', 'Visitor Name', 'Meeting With', 'Created At'));
                $this->line(str_repeat('-', 80));

                foreach ($interactionsToFix as $interaction) {
                    $this->line(sprintf('%-15s %-20s %-15s %-20s', 
                        $interaction->interaction_id,
                        $interaction->name_entered ?? 'N/A',
                        $interaction->meeting_with ?? 'N/A',
                        $interaction->created_at
                    ));
                }
                $this->line(str_repeat('-', 80));
                $this->newLine();

                // Update each interaction
                $updatedCount = 0;
                foreach ($interactionsToFix as $interaction) {
                    // Get the latest remark for this interaction
                    $latestRemark = DB::table('remarks')
                        ->where('interaction_id', $interaction->interaction_id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($latestRemark) {
                        // Update the interaction
                        DB::table('interaction_history')
                            ->where('interaction_id', $interaction->interaction_id)
                            ->update([
                                'is_completed' => true,
                                'completed_at' => $latestRemark->created_at,
                                'completed_by' => $latestRemark->added_by,
                                'updated_at' => now()
                            ]);

                        $updatedCount++;
                        $this->info("✅ Updated interaction ID: {$interaction->interaction_id} (completed by user: {$latestRemark->added_by})");
                    }
                }

                $this->newLine();
                $this->info("🎉 Successfully updated {$updatedCount} interactions!");
                $this->newLine();
            } else {
                $this->info('✅ No interactions need to be fixed. All interactions with remarks are already marked as completed.');
                $this->newLine();
            }

            // Verify the fix
            $this->info('🔍 Verification - Checking for any remaining issues...');
            $remainingIssues = DB::table('interaction_history')
                ->whereIn('interaction_id', function($query) {
                    $query->select('interaction_id')
                          ->from('remarks')
                          ->whereNotNull('interaction_id');
                })
                ->where('is_completed', false)
                ->count();

            if ($remainingIssues == 0) {
                $this->info('✅ All interactions with remarks are now properly marked as completed!');
            } else {
                $this->error("❌ Warning: {$remainingIssues} interactions still have issues.");
            }

            // Show summary statistics
            $this->newLine();
            $this->info('📊 Summary Statistics:');
            $this->line(str_repeat('-', 50));
            
            $totalInteractions = DB::table('interaction_history')->count();
            $completedInteractions = DB::table('interaction_history')->where('is_completed', true)->count();
            $pendingInteractions = DB::table('interaction_history')->where('is_completed', false)->count();
            $interactionsWithRemarks = DB::table('interaction_history')
                ->whereIn('interaction_id', function($query) {
                    $query->select('interaction_id')
                          ->from('remarks')
                          ->whereNotNull('interaction_id');
                })
                ->count();

            $this->line("Total Interactions: {$totalInteractions}");
            $this->line("Completed Interactions: {$completedInteractions}");
            $this->line("Pending Interactions: {$pendingInteractions}");
            $this->line("Interactions with Remarks: {$interactionsWithRemarks}");
            $this->line(str_repeat('-', 50));

            $this->newLine();
            $this->info('🎉 Database cleanup completed successfully!');

        } catch (\Exception $e) {
            $this->error("❌ Error during cleanup: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
