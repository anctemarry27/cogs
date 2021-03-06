<?php
/**
 * Og Framework Run Configuration
 *
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Providers\CoreServiceProvider;
use Og\Providers\ErrorHandlingServiceProvider;
use Og\Providers\RoutingServiceProvider;
use Og\Providers\SessionServiceProvider;

return [
    # HTML META entries
    'meta'         => [
        'name'        => 'Og: A PHP Micro-Framework',
        'description' => '4th Generation Implementation of the Radium Framework',
    ],

    #
    #  The framework session name
    #
    'session_name' => 'COGS',

    #
    #  Service Providers
    #
    #  Note that order is significant.
    'providers'    => [
        ErrorHandlingServiceProvider::class,
        SessionServiceProvider::class,
        CoreServiceProvider::class,
        RoutingServiceProvider::class,
    ],

    #
    # Middleware options:
    #   '<class name>'          - middleware class to add
    #   [<class_name>,'<path>]  - format for addPath
    #
    'middleware'   => [
        'AuthMiddleware',
        ['HelloWorldMiddleware', '/hello',],
        'RoutingMiddleware',
        'EndOfLineMiddleware',
        'ElapsedTimeMiddleware',
    ],

    #
    # core dependencies
    #
    'dependencies' => [
        # <abstract> => <class_name>||<callable>[, ...] 
    ],
];
