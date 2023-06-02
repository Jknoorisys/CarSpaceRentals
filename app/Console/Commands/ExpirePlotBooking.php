<?php

namespace App\Console\Commands;

use App\Models\Bookings;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExpirePlotBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire plot bookings on specified date/time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the current date and time
        $now = Carbon::now()->format('Y-m-d');

        // Find all plot bookings where the expiration date/time is equal to the current date/time
        $bookings = Bookings::where('park_out_date', $now)->where('status', '=', 'active')->get();

        foreach ($bookings as $booking) {
            // Expire the plot booking
            $booking->car_id = '';
            $booking->status = 'expired';
            $booking->updated_at = Carbon::now();
            $booking->save();
        }

        $this->info('Plot bookings expired successfully.');
    }
}
