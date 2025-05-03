<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Serve extends Command
{
    protected $signature = 'serve';
    protected $description = 'Serve the application on the PHP built-in server';

    public function handle()
    {
        // Start the Node.js server automatically
        Artisan::call('node:serve');

        // Start the Laravel server
        $this->line('Starting Laravel server...');
        Artisan::call('serve');
    }
}
