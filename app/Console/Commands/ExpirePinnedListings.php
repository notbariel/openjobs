<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class ExpirePinnedListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pinned listings after 24 hours.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $expired = [];

        $listings = Listing::pinned()
            ->paid()
            ->get();

        $now = now();
        foreach ($listings as $listing) {
            if ($now->diffInHours($listing->paid_at) >= 24) {
                $listing->update([
                    'is_pinned' => false,
                ]);
                $expired[] = $listing->id;
            }
        }

        if (count($expired)) {
            info('expired ' . count($expired) . ' listings', $expired);
        }

        return 0;
    }
}
