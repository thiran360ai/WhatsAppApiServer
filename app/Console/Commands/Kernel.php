<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // You register your custom command here
    ];

    protected function schedule(Schedule $schedule)
    {
        // You can schedule artisan commands here
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
