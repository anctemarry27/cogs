<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use FastRoute\RouteCollector;
use Zend\Diactoros\Response;

/** @var routeCollector $routes */

$routes->addRoute(['GET', 'POST'], '/test/{name}',
    function ($name)
    {
        return "Test Route [$name]";
    }
);
