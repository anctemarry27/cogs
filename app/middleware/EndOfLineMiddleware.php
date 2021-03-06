<?php namespace App\Middleware;

/** 
 * Example Middleware: End of Line
 * 
 * 
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EndOfLineMiddleware extends Middleware
{
    /**
     * @param Request  $request
     * @param Response $response
     * @param callable $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = NULL)
    {
        $response->getBody()->write('<div>End of Line.</div>' . PHP_EOL);

        // delegate to parent
        return parent::__invoke($request, $response, $next);
    }
}
