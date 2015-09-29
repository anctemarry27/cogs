<?php
/**
 * Route definitions.
 *
 * @Note    : In general, routes should return with one of the following:
 *            a. A non-NULL value > 0 or TRUE continues the routing middleware.
 *            b. A NULL value or FALSE handles the Response and terminates the middleware.
 *            c. Redirect or halt the application with an error etc.
 *
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use FastRoute\RouteCollector;
use Og\Interfaces\ContainerInterface;
use Og\Support\Cogs\Collections\Input;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

const APP_NAMESPACE = 'App\Http\Controllers\\';

/** @var routeCollector $routes */

$routes->addRoute(['GET', 'POST'], '/',
    function (ContainerInterface $di, Request $request, Response $response)
    {
        /** @var Response $response */

        return $response->getBody()->write('Root Route, Baby!');
    }
);

$routes->addRoute(['GET'], '/controller', APP_NAMESPACE . "HomeController@index");
$routes->addRoute(['GET'], '/controller/{name}', APP_NAMESPACE . "HomeController@index");

$routes->addRoute(['GET', 'POST'], '/test/{name}',
    function (Input $input, Response $response)
    {
        # Http response
        $response->getBody()->write("Test Route [{$input['name']}]");

        # test response as text 
        return "Test Route [{$input['name']}]";
    }
);
