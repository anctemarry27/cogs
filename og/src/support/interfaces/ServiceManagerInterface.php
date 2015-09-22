<?php namespace Og\Support\Interfaces;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface ServiceManagerInterface
{
    /**
     * Adds and then registers a service provider.
     * 
     * @param $provider
     *
     * @return mixed
     */
    function addAndRegister($provider);
    
    /**
     * Adds a service provider to the container
     *
     * @param string $provider
     * @param bool   $as_dependency
     *
     * @return $this
     */
    function add($provider, $as_dependency = FALSE);

    /**
     * Boot all registered providers.
     */
    function bootAll();

    /**
     * @return static
     */
    function loadConfiguration();

    /**
     * Determines if a definition is registered via a service provider.
     *
     * @param  string $alias
     *
     * @return boolean
     */
    //function isInServiceProvider($alias);

    /**
     * @param $provider
     *
     * @return void
     */
    function registerServiceProvider($provider);

    /**
     * @return void
     */
    function registerServiceProviders();
}
