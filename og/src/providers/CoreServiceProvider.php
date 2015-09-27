<?php namespace Og\Providers;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Illuminate\Container\Container;
use Og\Context;
use Og\Events;
use Og\Forge;
use Og\Paths;
use Og\Support\Cogs\Collection;

class CoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Forge $di */
        $di = $this->container;

        # register collections and paths
        $di->add(['collection', Collection::class]);
        $di->add(['paths', Paths::class]);

        # cogs principle service container 
        $di->singleton(['container', Forge::class],
            function () use ($di) { return $di; });

        //# Core Configuration
        //$di->singleton(['config', Config::class], 
        //    function () { return new Config; });
        //config()->importFolder(APP_CONFIG);

        # illuminate/container service container 
        $di->singleton(['ioc', Container::class],
            function () { return Forge::getInstance()->service('getInstance'); });

        # register Application context
        $di->singleton(['context', Context::class],
            function () use ($di) { return new Context($di); });

        # register cogs principle event dispatcher
        $di->singleton(['events', Events::class],
            function () use ($di) { return new Events($di); });

        $this->provides[] = [
            Collection::class,
            Context::class,
            Events::class,
            Paths::class,
        ];
    }
}
