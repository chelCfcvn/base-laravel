<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Providers\ServiceRegister\Admin;
use App\Providers\ServiceRegister\Common;
use App\Providers\ServiceRegister\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Admin::register($this->app);
        Common::register($this->app);
        User::register($this->app);

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            Handler::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
