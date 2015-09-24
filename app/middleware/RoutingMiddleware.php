<?php namespace Og\App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Router;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RoutingMiddleware extends Middleware
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
        ///** @var Router $router */
        //$router = di('router');
        
        $response->getBody()->write("<div>I should be routing by now.</div>");

        return parent::__invoke($request, $response, $next);
    }
}
