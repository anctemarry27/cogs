<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Cogs\Collections\Input;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RoutingMiddleware extends Middleware
{
    # route match status
    const NOT_FOUND          = 0;
    const FOUND              = 1;
    const METHOD_NOT_ALLOWED = 2;

    /**
     * @param Request       $request
     * @param Response      $response
     * @param callable|NULL $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = NULL)
    {
        # Search for a route match using the registered routing object.
        #
        #   $status will be in the following:
        #
        #       NOT_FOUND = 0;
        #       FOUND = 1;
        #       METHOD_NOT_ALLOWED = 2;
        #
        #   $action holds the callable.
        #
        #   $parameters is a key=>value array of template variables.
        #
        $result = $this->di->make('routing')->match();

        # if count($result) == 3 then collect all three route values
        # else get the status and null the rest
        if (count($result) == 3)
            list($status, $action, $parameters) = $result;
        else
        {
            # an error has occurred - most likely static::NOT_FOUND or static::METHOD_NOT_ALLOWED 
            $status = $result[0];
            $action = $parameters = NULL;
        }

        # Get the request parameters - coerce into an array if necessary.
        $parameters = (array) $parameters;

        switch ($status)
        {
            case static::NOT_FOUND:
            {
                # @TODO ... 404 Not Found
                #
                $response->getBody()
                         ->write("<span style='color:maroon'><b>404 Error</b></span> - <i>Page not found.</i>");

                return $response->withStatus(404);
                break;
            }
            case static::METHOD_NOT_ALLOWED:
            {
                #
                # $action will be an array of valid methods.
                # @TODO ... 405 Method Not Allowed
                #
                break;
            }
        }

        #
        # Register the Routing states into the container.
        # This is required so that the container can inject them
        # into the action method.
        # 
        $this->di->add(['ServerRequestInterface', Request::class], $request);
        $this->di->add(['ResponseInterface', Response::class], $response);
        $this->di->add(Input::class, new Input($parameters));

        # call the route with dependency injection
        if ($this->di->call($action, $parameters))
            # the target returned a valid response, so link it
            return parent::__invoke($request, $response, $next);
        else
            # otherwise, return the response and short-circuit the middleware
            return $response;
    }
}
