<?php namespace Og\Support\Providers;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Router;
use Og\Support\Abstracts\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    /**
     * Use the register method to register items with the container via the
     * protected `$this->di` property or the `getContainer` method
     * from trait `WithContainer`.
     *
     * @return void
     */
    public function register()
    {
        $di = $this->container;

        $di->singleton(['router', Router::class], function () { return new Router; });

        $this->provides[] = [
            Router::class,
        ];

    }
}
