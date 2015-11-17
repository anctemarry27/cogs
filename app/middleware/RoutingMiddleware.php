<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Routing;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RoutingMiddleware extends Middleware
{
    /**
     * The Routing object is obtained from the Forge.
     *
     * @var Routing
     */
    private $routing;

    /**
     * Routing Middleware entry point.
     *
     * Route determination and execution is handled entirely by this object.
     *
     * @param Request       $request
     * @param Response      $response
     * @param callable|NULL $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = NULL)
    {
        // we need to get the routing object here due to the strict
        // middleware __invoke method signature. 
        $this->routing = $this->forge->make(Routing::class);

        // evaluate the route and return state
        list($status, $action, $input) = $this->routing->evaluate_route($this->routing->match());
        
        // verify that the route is valid
        $error_response = $this->routing->getRouteError($response, $status);

        // has an error been detected?
        if (is_null($error_response))
        {
            // No, so execute the route
            return $this->routing->executeRouteAction($input, $action)
                ? parent::__invoke($request, $response, $next)
                : $response;
        }
        else
            // otherwise return the error response
            return $error_response;
    }

}
