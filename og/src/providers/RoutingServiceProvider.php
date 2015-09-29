<?php namespace Og\Providers;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Routing;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\Http\Request as HttpRequest;
use Zend\Stratigility\Http\Response as HttpResponse;

class RoutingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->container->singleton(['routing', Routing::class], function ()
        {
            $routing = new Routing(
                new HttpRequest(ServerRequestFactory::fromGlobals()),
                new HttpResponse(new Response)
            );
            
            $routing->make(HTTP . "routes.php");

            return $routing;
        });

        $this->provides += [Routing::class,];
    }
}
