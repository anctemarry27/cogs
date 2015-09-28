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
use Og\Support\Cogs\Collections\Collection;

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
        $di->singleton(['container', Forge::class], new Forge);

        # illuminate/container service container 
        $di->singleton(['ioc', Container::class],
            function () { return Forge::getInstance()->service('getInstance'); });

        # register Application context
        $di->singleton(['context', Context::class], new Context);

        # register cogs principle event dispatcher
        $di->singleton(['events', Events::class], new Events);

        $this->provides[] = [
            Collection::class,
            Context::class,
            Events::class,
            Paths::class,
        ];
    }
}
