<?php namespace Og\Providers;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class MiddlewareServiceProvider extends ServiceProvider
{

    public function boot()
    {

    }

    /**
     * Use the register method to register items with the container via the
     * protected `$this->forge` property or the `getContainer` method
     * from trait `WithContainer`.
     *
     * @return void
     */
    public function register()
    {
        //$this->middleware = $middleware = new Middleware($this->container);
        //$this->container->singleton(['middleware', Middleware::class,], $middleware);
        //
        //# create and register the server, request and response
        //$server = Server::createServer($this->middleware, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
        //
        //# register the server
        //$this->container->singleton(['server', Server::class], $server);
        //
        //# register request and response
        //$this->container->add(['request', Request::class,],
        //    function () use ($server)
        //    {
        //        return new Request($server->{'request'});
        //    }
        //);
        //
        //# register the response
        //$this->container->add(['response', Response::class,],
        //    function () use ($server)
        //    {
        //        return new Response($server->{'response'});
        //    }
        //);

    }
}
