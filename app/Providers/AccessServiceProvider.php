<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AccessServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Repositories\Order\OrderConfirmationNumberGeneratorContract::class,
            \App\Repositories\Order\OrderConfirmationNumberGeneratorRepository::class
        );

        $this->app->bind(
            \App\Repositories\Ticket\TicketCodeGeneratorContract::class,
            \App\Repositories\Ticket\TicketCodeGeneratorRepository::class
        );
    }
}