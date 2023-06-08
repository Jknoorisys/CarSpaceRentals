<?php

namespace App\Console\Commands;

use App\Models\Bookings;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActivatePlotBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate plot bookings on specified date/time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the current date and time
        $now = Carbon::now()->format('Y-m-d');

        // Find all plot bookings where the activation date/time is equal to the current date/time
        $bookings = Bookings::where('park_in_date', $now)->where('status', '=', 'upcoming')->get();

        foreach ($bookings as $booking) {
            // Expire the plot booking
            $booking->status = 'active';
            $booking->updated_at = Carbon::now();
            $booked = $booking->save();

            if ($booked) {
                $car_id = $booking->car_id ? $booking->car_id : '';
                if (!empty($car_id)) {
                    DB::table('cars')->where('id', '=', $car_id)->update(['is_assgined' => 'yes']);
                }
            }
        }

        $this->info('Plot bookings activated successfully.');
    }
}
