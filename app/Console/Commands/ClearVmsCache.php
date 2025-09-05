<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearVmsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vms:clear-cache {--type=all : Type of cache to clear (all, admin, frontdesk, statistics)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear VMS CRM cache for better performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        switch ($type) {
            case 'admin':
                $this->clearAdminCache();
                break;
            case 'frontdesk':
                $this->clearFrontDeskCache();
                break;
            case 'statistics':
                $this->clearStatisticsCache();
                break;
            case 'all':
            default:
                $this->clearAllCache();
                break;
        }

        $this->info("VMS cache cleared successfully for type: {$type}");
    }

    private function clearAdminCache()
    {
        // Clear admin-specific caches
        $this->info('Clearing admin cache...');
        
        // Clear paginated data caches (first 10 pages)
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget("admin_visitors_page_{$i}");
            Cache::forget("admin_interactions_page_{$i}");
            Cache::forget("admin_remarks_page_{$i}");
        }
    }

    private function clearFrontDeskCache()
    {
        // Clear front desk caches
        $this->info('Clearing front desk cache...');
        
        // Clear general caches
        Cache::forget('employees_list');
        Cache::forget('locations_list');
        
        // Note: User-specific caches are cleared when data changes
    }

    private function clearStatisticsCache()
    {
        // Clear statistics caches
        $this->info('Clearing statistics cache...');
        
        Cache::forget('total_visitors');
        Cache::forget('total_interactions');
        Cache::forget('total_employees');
        Cache::forget('today_interactions');
    }

    private function clearAllCache()
    {
        // Clear all VMS caches
        $this->info('Clearing all VMS cache...');
        
        $this->clearAdminCache();
        $this->clearFrontDeskCache();
        $this->clearStatisticsCache();
        
        // Clear any other VMS-related caches
        Cache::flush();
    }
}
