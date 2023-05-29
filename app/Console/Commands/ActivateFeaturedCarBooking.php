<?php

namespace App\Console\Commands;

use App\Models\Cars;
use App\Models\FeaturedCars;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ActivateFeaturedCarBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'car:featured';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate featured car booking on specified date/time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the current date and time
        $now = Carbon::now()->format('Y-m-d');

        // Find all featured car bookings where the activation date/time is equal to the current date/time
        $bookings = FeaturedCars::where('start_date', $now)->where('status', '=', 'upcoming')->get();

        foreach ($bookings as $booking) {
            // Activate the featured car booking
            $booking->status = 'featured';
            $booking->updated_at = Carbon::now();
            $booked = $booking->save();

            if ($booked) {
                Cars::where('id', '=', $booking->car_id)->update(['is_featued' => 'yes', 'updated_at' => Carbon::now()]);
            }
        }

        $this->info('Featured Car bookings activated successfully.');
    }
}
