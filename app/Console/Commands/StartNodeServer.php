<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StartNodeServer extends Command
{
    protected $signature = 'node:serve';
    protected $description = 'Start the Node.js server';

    public function handle()
    {
        $this->info('🚀 Starting Node.js server...');

        // Adjust this path to your actual JS file location
        $process = new Process(['node', base_path('nodejs/server.js')]);

        // Optional: Run in background
        $process->start();

        $this->info('✅ Node.js server started.');
    }
}
