<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Application;
use Og\Forge;
use Og\Interfaces\ContainerInterface;
use Og\Interfaces\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Stratigility\MiddlewarePipe;

class Middleware extends MiddlewarePipe implements MiddlewareInterface
{
    /** @var ContainerInterface|Forge */
    protected $di;

    public function __construct(ContainerInterface $di)
    {
        parent::__construct();
        $this->di = $di;
    }

    /**
     * @param Request       $request
     * @param Response      $response
     * @param callable|NULL $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = NULL)
    {
        # TODO: Middleware Pre-Hook?

        # call the app middleware before event
        $this->di->make('events')->fire(Application::NOTIFY_MIDDLEWARE, [$this, $request, $response]);

        # call parent middleware
        return parent::__invoke($request, $response, $next);

        # TODO: Middleware Post-Hook?
    }

    /**
     * @param $abstract
     */
    public function add($abstract)
    {
        # if no namespace is evident then inject the Middleware namespace.
        $abstract = $this->decorate_namespace($abstract);

        $this->pipe(new $abstract($this->di));
    }

    /**
     * @param $abstract
     *
     * @return string
     */
    private function decorate_namespace($abstract)
    {
        # get the segments so we can check for a namespace
        $segments = explode('\\', $abstract);

        # if no namespace is evident then inject the Middleware namespace.
        return count($segments) == 1 ? __NAMESPACE__ . "\\$abstract" : $abstract;
    }

    /**
     * @param $abstract
     * @param $path
     */
    public function addPath($abstract, $path)
    {
        # if no namespace is evident then inject the Middleware namespace.
        $abstract = $this->decorate_namespace($abstract);

        $this->pipe($path, new $abstract($this->di));
    }

    /**
     * @param array $middlewares - an array of middlewares
     */
    public function installMiddlewares(array $middlewares)
    {
        foreach ($middlewares as $middleware)
        {
            is_array($middleware)
                # array as ['<middleware>','<path>']
                ? $this->addPath($middleware[0],$middleware[1])
                # '<middleware>'
                : $this->add($middleware);
        }

    }

}
