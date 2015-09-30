<?php
/**
 * Og Framework Run Configuration
 *
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Providers\CoreServiceProvider;
use Og\Providers\RoutingServiceProvider;

return [
    # Reserved for the framework.
    'meta' => [
        'name' => 'Og: A PHP Micro-Framework',
        'version' => '0.1.0@dev',
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
    'providers' => [
        //SessionServiceProvider::class,
        CoreServiceProvider::class,
        RoutingServiceProvider::class,
    ],
    #
    # core framework middleware
    #
    'middleware' => [
        # <middleware>[, ...]            
    ],
    #
    # core dependencies
    #
    'dependencies' => [
        # <abstract> => <class_name>||<callable>[, ...] 
    ],
];
