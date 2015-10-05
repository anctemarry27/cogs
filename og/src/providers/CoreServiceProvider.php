<?php namespace Og\Providers;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Illuminate\Container\Container as IlluminateContainer;
use Og\Context;
use Og\Events;
use Og\Forge;
use Og\Interfaces\ContainerInterface;
use Og\Paths;

class CoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Forge $di */
        $di = $this->forge;

        # register collections and paths
        # $forge->add(['collection', Collection::class]);
        $di->add(['paths', Paths::class]);

        # cogs principle service container 
        $di->singleton([ContainerInterface::class, Forge::class], new Forge);

        # illuminate/container service container 
        $di->singleton(['ioc', IlluminateContainer::class],
            function () { return Forge::getInstance()->container(); });

        # register Application context
        $di->singleton(['context', Context::class], new Context);

        # register cogs principle event dispatcher
        $di->singleton(['events', Events::class], new Events);

        $this->provides += [Context::class, Events::class, Paths::class,];
    }
}
