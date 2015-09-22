<?php namespace Og\App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelloWorldMiddleware extends Middleware
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
        if ( ! in_array($request->getUri()->getPath(), ['/', ''], TRUE))
            return $next($request, $response);

        $response->getBody()->write('<div>Hello world!<br /></div>' . PHP_EOL);

        return parent::__invoke($request, $response, $next);
    }
}
