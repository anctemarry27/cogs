<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Events;
use Og\Routing;
use Og\Support\Cogs\Collections\Input;
use Og\Support\Str;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RoutingMiddleware extends Middleware
{
    # route match status
    const NOT_FOUND          = 0;
    const FOUND              = 1;
    const METHOD_NOT_ALLOWED = 2;

    /** @var Events */
    private $events;

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
        $this->events = $this->forge->get('events');

        # we need to get the routing object here due to the strict
        # middleware __invoke method signature. 
        $this->routing = $this->forge->make(Routing::class);

        # evaluate the route and return state
        list($status, $action, $input) = $this->evaluate_route($this->routing->match());

        # Get the request input - coerce into an array if necessary.
        # $input = (array) $input;

        $error_result = $this->verify_route($response, $status);

        if ($error_result)
            return $error_result;

        # continue or stop
        return $this->execute_action($request, $response, $input, $action)
            ? parent::__invoke($request, $response, $next)
            : $response;
    }

    /**
     * @param $result
     *
     * @return array
     */
    private function evaluate_route($result)
    {
        # if count($result) == 3 then collect all three route values
        # else get the status and null the rest
        if (count($result) == 3)
        {
            list($status, $action, $input) = $result;

            return [$status, $action, (array) $input];
        }
        else
        {
            # an error has occurred - most likely static::NOT_FOUND or static::METHOD_NOT_ALLOWED 
            $status = $result[0];
            $action = $input = NULL;

            return [$status, $action, (array) $input];
        }
    }

    /**
     * @param Response $response
     * @param          $status
     *
     * @return null|Response
     */
    private function verify_route(Response $response, $status)
    {
        switch ($status)
        {
            case static::NOT_FOUND:
            {
                # @TODO ... 404 Not Found
                #
                $error = $this->set_error($response, 404);
                break;
            }
            case static::METHOD_NOT_ALLOWED:
            {
                #
                # $action will be an array of valid methods.
                # @TODO ... 405 Method Not Allowed
                #
                $error = $this->set_error($response, 405);
                break;
            }
            default:
                $error = NULL;
                break;
        }

        return $error;
    }

    /**
     * @param Response $response
     * @param          $code
     *
     * @return Response
     */
    private function set_error(Response $response, $code)
    {
        $error_message = Str::http_code($code);

        $response->getBody()
                 ->write("<span style='color:maroon'><b>$code Error</b></span> - <i>$error_message.</i>");

        return $response->withStatus($code);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param          $input
     * @param          $action
     *
     * @return mixed
     */
    private function execute_action(Request $request, Response $response, $input, $action)
    {
        # register the Request/Response/Input objects 
        $this->forge->add(Input::class, new Input($input));

        # before dispatch event
        $this->events->fire(OG_BEFORE_ROUTE_DISPATCH, [$request, $response]);

        # transfer control to the route handler
        $result = $this->forge->call($action, $input);

        # after dispatch event
        $this->events->fire(OG_AFTER_ROUTE_DISPATCH, [$request, $response]);

        return $result;
    }
}
