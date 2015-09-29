<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\Dispatcher;
use Og\Support\Cogs\Collections\Input;
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

        # grab the original request if not already
        //$request = method_exists($request, 'getOriginalRequest') ? $request->getOriginalRequest() : $request;

        //$this->routing = new Routing($request, $response);
        //$this->routing->make(HTTP . "routes.php");

        # obtain the routing object created by RoutingServiceProvider
        $route = $this->di->make('routing')->match();

        # status will be in the following:
        #   NOT_FOUND = 0;
        #   FOUND = 1;
        #   METHOD_NOT_ALLOWED = 2;
        $status = $route[0];

        switch ($status)
        {
            case Dispatcher::NOT_FOUND:
                #@TODO ... 404 Not Found
                $response->getBody()
                         ->write("<span style='color:maroon'><b>404 Error</b></span> - <i>Page not found.</i>");

                return $response->withStatus(404);
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                # $allowedMethods = $routeInfo[1];
                # @TODO ... 405 Method Not Allowed
                break;
        }

        # get the target callable
        $target = $route[1];

        # get the request parameters
        $route[2] = is_array($route[2]) ? $route[2] : [$route[2]];

        # assign dependencies to the di
        $this->di->add(['ServerRequestInterface', Request::class], $request);
        $this->di->add(['ResponseInterface', Response::class], $response);
        $this->di->add(Input::class, new Input($route[2]));

        # call the route with dependency injection
        if ($this->di->call($target, $route[2]))
            # the target returned a valid response, so link it
            return parent::__invoke($request, $response, $next);
        else
            # otherwise, return the response and short-circuit the middleware
            return $response;
    }
}
