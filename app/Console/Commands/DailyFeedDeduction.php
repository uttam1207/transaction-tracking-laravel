<?php

namespace App\Console\Commands;

use App\Services\FeedDeductionService;
use Illuminate\Console\Command;

class DailyFeedDeduction extends Command
{
    protected $signature   = 'dairy:feed-deduction {--force : Run even if already deducted today}';
    protected $description = 'Deduct daily feed quantities from inventory stock based on active feed plans';

    public function handle(FeedDeductionService $service): int
    {
        $this->info('Running daily feed deduction...');

        $result = $service->runDeduction(recordedBy: 1); // system user

        if (!empty($result['deducted'])) {
            $this->info('Deducted:');
            foreach ($result['deducted'] as $d) {
                $this->line("  ✓ {$d['feed_name']}: {$d['deducted']} {$d['unit']} deducted (need: {$d['daily_need']} {$d['unit']})");
            }
        }

        if (!empty($result['skipped'])) {
            $this->warn('Already deducted today (skipped):');
            foreach ($result['skipped'] as $s) {
                $this->line("  ~ {$s['feed_name']}: {$s['daily_need']} {$s['unit']}");
            }
        }

        if (!empty($result['not_found'])) {
            $this->warn('No matching inventory item found:');
            foreach ($result['not_found'] as $n) {
                $this->line("  ! {$n['feed_name']}: {$n['daily_need']} kg needed but no inventory item matched");
            }
        }

        if (!empty($result['no_stock'])) {
            $this->error('Insufficient stock (partial deduction):');
            foreach ($result['no_stock'] as $ns) {
                $this->line("  ⚠ {$ns['feed_name']}: needed {$ns['daily_need']} {$ns['unit']}, only {$ns['available']} available — deducted {$ns['deducted']} {$ns['unit']}");
            }
        }

        $total = count($result['deducted']);
        $this->info("Done. {$total} feed item(s) deducted.");

        return Command::SUCCESS;
    }
}
