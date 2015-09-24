<?php

namespace Illuminate\Events;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('events', function (Container $app) {
            return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                return $app->make('Illuminate\Contracts\Queue\Factory');
            });
        });
    }
}
