<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('medcapsule:setup', function () {
    $this->info('Setting up MedCapsule...');
    
    // Clear all caches
    $this->call('config:clear');
    $this->call('route:clear');
    $this->call('view:clear');
    
    // Run migrations
    $this->call('migrate');
    
    // Cache configuration for performance
    $this->call('config:cache');
    $this->call('route:cache');
    
    $this->info('MedCapsule setup completed successfully!');
})->purpose('Setup MedCapsule application');

Artisan::command('medcapsule:clear-cache', function () {
    $this->info('Clearing all MedCapsule caches...');
    
    $this->call('config:clear');
    $this->call('route:clear');
    $this->call('view:clear');
    $this->call('cache:clear');
    
    $this->info('All caches cleared successfully!');
})->purpose('Clear all application caches');