<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use FastRoute\RouteCollector;
use Zend\Stratigility\Http\Request;
use Zend\Stratigility\Http\Response;

/** @var routeCollector $routes */


$routes->addRoute(['GET', 'POST'], '/test/{name}',
    function (Request $request, Response $response)
    {
        $name = $request->getAttribute('name');

        $response->getBody()->write("Test Route [$name]");

        return $response;
    });


