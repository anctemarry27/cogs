<?php

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\RouteCollector;
use Og\Support\Collections\Input;
use Zend\Diactoros\Response;

/** @var routeCollector $routes */

$routes->addRoute(['GET', 'POST'], '/test/{name}',
    function (Input $input, Response $response)
    {
        # Http response
        $response->getBody()->write("Test Route [{$input['name']}]");

        # test response as text 
        return "Test Route [{$input['name']}]";
    }
);
