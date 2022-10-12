<?php

namespace Adminka\AmoSync;

use Adminka\AmoSync\Console\Commands\AmoMigrate;
use Adminka\AmoSync\Console\Commands\AmoSync;
use Illuminate\Support\ServiceProvider;

class AmoSyncServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . "/../database/migrations");
        if ($this->app->runningInConsole()) {
            $this->commands([
                AmoSync::class,
                AmoMigrate::class
            ]);
        }
    }

    public function register()
    {
        //parent::register(); // TODO: Change the autogenerated stub
    }
}
