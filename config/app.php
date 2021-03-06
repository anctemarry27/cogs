<?php namespace App;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

return [
    'meta' => [
        'name'        => 'Og: A PHP Micro-Framework',
        'description' => '4th Generation Implementation of the Radium Framework',
    ],
    'title'    => 'COGS - A Personal PHP Application Framework',
    'version'  => '0.1.0@alpha',
    'debug'    => TRUE,
    'encoding' => 'UTF-8',
    'mode'     => 'DEVELOPMENT',
    'timezone' => 'America/Vancouver',

    'providers' => [

    ],

    'middleware' => [
        Middleware\AuthMiddleware::class,
        Middleware\RoutingMiddleware::class,
        Middleware\EndOfLineMiddleware::class,
        Middleware\ElapsedTimeMiddleware::class,
    ],
];
