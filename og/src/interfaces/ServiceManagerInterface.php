<?php namespace Og\Interfaces;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface ServiceManagerInterface
{
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
     * Adds and then registers a service provider.
     *
     * @param $provider
     *
     * @return mixed
     */
    function addAndRegister($provider);

    /**
     * Boot all registered providers.
     */
    function bootAll();

    /**
     * @param array|NULL $providers
     *
     * @return static
     */
    function loadConfiguration($providers);

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
