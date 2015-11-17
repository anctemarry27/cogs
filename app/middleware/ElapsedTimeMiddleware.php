<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ElapsedTimeMiddleware extends Middleware
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
        $elapsed_time = elapsed_time_since_request();
        $response->getBody()->write("<div>Elapsed time : $elapsed_time</div>" . PHP_EOL);

        // delegate to parent
        return parent::__invoke($request, $response, $next);
    }
}
