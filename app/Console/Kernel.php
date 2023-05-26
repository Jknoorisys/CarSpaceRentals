<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Plot Booking
        Commands\ActivatePlotBooking::class,
        Commands\ExpirePlotBooking::class,

        // Featured Car Booking
        Commands\ActivateFeaturedCarBooking::class,
        Commands\ExpireFeaturedCarBooking::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Plot Booking
        $schedule->command('booking:activate')->dailyAt('00:02');
        $schedule->command('booking:expire')->dailyAt('23:58');

        // Featured Car Booking
        $schedule->command('car:featured')->dailyAt('00:02');
        $schedule->command('car:unfeatured')->dailyAt('23:58');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
