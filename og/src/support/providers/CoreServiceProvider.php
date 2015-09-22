<?php namespace Og\Support\Providers;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Illuminate\Container\Container;
use Og\Collection;
use Og\Config;
use Og\Context;
use Og\EventsDispatcher;
use Og\Forge;
use Og\Paths;
use Og\Support\Abstracts\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //$this->di->singleton(['app', Application::class], new Application($this->di));
    }

    public function register()
    {
        /** @var Forge $di */
        $di = $this->container;

        $di->singleton(['container', Forge::class],
            function () use ($di)
            {
                return $di;
            }
        );

        $di->singleton(['ioc', Container::class],
            function ()
            {
                return Forge::getInstance()->service('getInstance');
            }
        );

        # Core Configuration
        $di->singleton(['config', Config::class], new Config);
        config()->importFolder(APP_CONFIG);

        # register Application context
        $di->add(['context', Context::class]);

        # register the Paths class
        $di->add(['collection', Collection::class]);
        
        $di->singleton(['events', EventsDispatcher::class,],
            function () use ($di)
            {
                return new EventsDispatcher($di);
            }
        );
        
        $di->add(['paths', Paths::class]);

        $this->provides[] = [
            Collection::class,
            Context::class,
            EventsDispatcher::class,
            Paths::class,
        ];
    }
}
