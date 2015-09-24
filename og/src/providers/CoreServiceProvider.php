<?php namespace Og\Providers;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Illuminate\Container\Container;
use Og\Abstracts\ServiceProvider;
use Og\Collection;
use Og\Config;
use Og\Context;
use Og\EventsDispatcher;
use Og\Forge;
use Og\Paths;

class CoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Forge $di */
        $di = $this->container;

        # register collections and paths
        $di->add(['collection', Collection::class]);
        $di->add(['paths', Paths::class]);

        # Core Configuration
        $di->instance(['config', Config::class], new Config);
        config()->importFolder(APP_CONFIG);

        # cogs principle service container 
        $di->singleton(['container', Forge::class],
            function () use ($di) { return $di; });

        # illuminate/container service container 
        $di->singleton(['ioc', Container::class],
            function () { return Forge::getInstance()->service('getInstance'); });

        # register Application context
        $di->singleton(['context', Context::class],
            function () use ($di) { return new Context($di); });
        
        # register cogs principle event dispatcher
        $di->singleton(['events', EventsDispatcher::class,],
            function () use ($di) { return new EventsDispatcher($di); });


        $this->provides[] = [
            Collection::class,
            Context::class,
            EventsDispatcher::class,
            Paths::class,
        ];
    }
}
