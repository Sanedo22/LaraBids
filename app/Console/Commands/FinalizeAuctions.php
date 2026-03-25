<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auction;

class FinalizeAuctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auctions:finalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalize ended auctions and assign winners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auction finalization process...');

        $endedAuctions = Auction::whereIn('status', ['active', 'pending'])
            ->where('end_time', '<=', now())
            ->get();

        if ($endedAuctions->isEmpty()) {
            $this->info('No auctions need finalization at this time.');
            return;
        }

        $count = 0;
        foreach ($endedAuctions as $auction) {
            if ($auction->status === 'pending') {
                $auction->update(['status' => 'closed', 'is_resubmitted' => false]);
                $this->info("Closed expired pending auction #{$auction->id}: {$auction->title}");
                $count++;
            } else {
                if ($auction->finalize()) {
                    $this->info("Finalized Auction #{$auction->id}: {$auction->title}");
                    $count++;
                }
            }
        }

        $this->info("Successfully finalized {$count} auctions.");
    }
}
