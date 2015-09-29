<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\Dispatcher;
use Og\Routing;
use Og\Support\Cogs\Collections\Input;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RoutingMiddleware extends Middleware
{

    /** @var Routing */
    private $routing;

    /**
     * @param Request       $request
     * @param Response      $response
     * @param callable|NULL $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = NULL)
    {

        //ddump(compact('request','response'));
        # grab the original request if not already
        $request = method_exists($request, 'getOriginalRequest') ? $request->getOriginalRequest() : $request;

        $this->routing = new Routing($request, $response);
        $this->routing->makeRoutes(HTTP . "routes.php");

        $route = $this->routing->dispatch();
        //$route = $this->routing->dispatch($request->getMethod(), $request->getUri()->getPath());

        # status will be in the following:
        #   NOT_FOUND = 0;
        #   FOUND = 1;
        #   METHOD_NOT_ALLOWED = 2;
        $status = $route[0];

        switch ($status)
        {
            case Dispatcher::NOT_FOUND:
                $response->getBody()
                         ->write("<span style='color:maroon'><b>404 Error</b></span> - <i>Page not found.</i>");

                return $response->withStatus(404);
                # ... 404 Not Found
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                # $allowedMethods = $routeInfo[1];
                #  ... 405 Method Not Allowed
                break;
        }

        # get the target callable
        $target = $route[1];

        # get the request parameters
        $route[2] = is_array($route[2]) ? $route[2] : [$route[2]];

        # append the request to the parameters
        //$params = sizeof($route[2]) > 0 ? [$route[2], $request, $response] : [$request, $response];

        # assign dependencies to the di
        di()->add(['ServerRequestInterface', Request::class], $request);
        di()->add(['ResponseInterface', Response::class], $response);
        di()->add(Input::class, new Input($route[2]));

        if (di()->call($target, $route[2]))
        {
            return parent::__invoke($request, $response, $next);
        }
        else
        {
            return $request;
        }
    }
}
