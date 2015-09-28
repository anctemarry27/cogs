<?php namespace Og\Providers;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Og\Router;

class RoutingServiceProvider extends ServiceProvider
{
    public function boot() { }

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
        $di->singleton(['router', Router::class], function () 
        {
            simpleDispatcher(
                function (routeCollector $routes)
                {
                    $path = APP . "http/routes.php";
                    include $path;
                },
                [
                    'routeParser' => 'FastRoute\\RouteParser\\Std',
                    'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
                    'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
                ]
            );    
            
            return new Router; 
        
        });
        $this->provides[] = [Router::class,];
    }
}
