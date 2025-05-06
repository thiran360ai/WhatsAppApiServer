<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // No need to bind controllers manually here
    }

    public function boot()
    {
        //
    }
}
