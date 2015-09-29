<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Application;
use Og\Interfaces\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Stratigility\MiddlewarePipe;

class Middleware extends MiddlewarePipe implements MiddlewareInterface
{
    /**
     * @param Request       $request
     * @param Response      $response
     * @param callable|NULL $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = NULL)
    {
        di('events')->fire(Application::NOTIFY_MIDDLEWARE, [$this, $request, $response]);

        return parent::__invoke($request, $response, $next);
    }

    /**
     * @param $abstract
     */
    public function add($abstract)
    {
        //$abstract = __NAMESPACE__ . "\\$abstract";
        $this->pipe(new $abstract);
    }

    /**
     * @param $path
     * @param $concrete
     */
    public function addPath($path, $concrete)
    {
        $this->pipe($path, $concrete);
    }

}
