<?php namespace Og;

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
use Og\Support\Collections\Input;

/** @var RouteCollector $routes */

$routes->addRoute(['GET'], '/controller[/{name}[/{action}]]', "HomeController@index");

$routes->addRoute(['GET', 'POST'], '/',
    function () { return \response('Root Route, Baby!'); });

//$routes->addRoute(['GET'], '/controller', "HomeController@index");

$routes->addRoute(['GET', 'POST'], '/test/{name}',
    function (Input $input) { return \response("Test Route [{$input['name']}]"); });
