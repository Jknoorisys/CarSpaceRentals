<?php

namespace App\Console\Commands;

use App\Models\FeaturedCars;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireFeaturedCarBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'car:unfeatured';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire featured car booking on specified date/time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       // Get the current date and time
       $now = Carbon::now()->format('Y-m-d');

       // Find all featured car bookings where the expiration date/time is equal to the current date/time
       $bookings = FeaturedCars::where('end_date', $now)->where('status', '=', 'featured')->get();

       foreach ($bookings as $booking) {
           // Expire the featured car booking
           $booking->status = 'unfeatured';
           $booking->updated_at = Carbon::now();
           $booking->save();
       }

       $this->info('Featured Car bookings expired successfully.');
    }
}
