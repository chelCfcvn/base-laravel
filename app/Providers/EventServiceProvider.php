<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    protected $listen = [
        'App\Events\MessageSent' => [
            'App\Listeners\SendMessageNotification',
        ],
    ];

    public function boot()
    {
        //
    }
}
