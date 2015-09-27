<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthMiddleware extends Middleware
{
    /** @var bool */
    private $logged_in = TRUE;

    /**
     * @param Request       $request
     * @param Response      $response
     * @param callable|NULL $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = NULL)
    {
        $msg = $this->logged_in ? "<div>You are logged in!</div>" : "<div>You need to login.</div>";
        $response->getBody()->write($msg);

        return $this->logged_in ? parent::__invoke($request, $response, $next) : $response;
    }

}
