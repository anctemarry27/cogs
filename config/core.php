<?php
/**
 * Og Framework Run Configuration
 *
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Providers\RoutingServiceProvider;

return [
    # Reserved for the framework.
    'meta' => [
        'name' => 'Og: A PHP Micro-Framework',
        'version' => '0.0.1@Alpha',
        'description' => '4th Generation Implementation of the Radium Framework',
    ],
    #
    # Providers are entered into the DIC as <abstract> and <class_name>.
    # i.e.: 'events' => 'Og\Events' is entered as 'events' and as 'Og\Events'.
    #
    'providers' => [
        //CoreServiceProvider::class,
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
