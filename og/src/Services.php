<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Interfaces\ContainerInterface;
use Og\Interfaces\ServiceManagerInterface;
use Og\Providers\ServiceProvider;

class Services implements ServiceManagerInterface
{
    /** @var bool */
    private $booted = FALSE;

    /** @var bool */
    private $configured = FALSE;

    /** @var Forge */
    private $di;

    /** @var array */
    private $providers = [];

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * Adds a service provider to the container
     *
     * @param string $provider
     * @param bool   $as_dependency
     *
     * @return $this
     */
    function add($provider, $as_dependency = FALSE)
    {
        $this->loadConfiguration();

        $this->providers[$provider] = ['registered' => FALSE, 'booted' => FALSE];;

        if ($as_dependency)
            $this->di->add([$provider, $provider]);

        return $this;
    }

    /**
     * @param $provider
     *
     * @return $this
     */
    function addAndRegister($provider)
    {
        $this->add($provider);
        $this->registerServiceProvider($provider);

        return $this;
    }

    /**
     * Boot all registered providers.
     */
    function bootAll()
    {
        if ( ! $this->booted)
        {
            foreach ($this->providers as $provider => $is)
            {
                if ( ! $this->isRegistered($provider))
                    $this->registerServiceProvider($provider);

                if (! $this->isBooted($provider))
                    $this->di->make($provider)->boot();
            }

            $this->booted = TRUE;
        }
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param $service_name
     *
     * @return bool
     */
    function isBooted($service_name)
    {
        return array_key_exists($service_name, $this->providers)
            ? $this->providers[$service_name]['booted']
            : FALSE;
    }

    /**
     * @param $service_name
     *
     * @return bool
     */
    function isRegistered($service_name)
    {
        return array_key_exists($service_name, $this->providers)
            ? $this->providers[$service_name]['registered']
            : FALSE;
    }

    /**
     * Load the Services configuration if not already loaded.
     */
    function loadConfiguration()
    {
        if ( ! $this->configured)
        {
            $this->configured = TRUE;
            $providers = $this->di['config']->get('core.providers');

            foreach ((array) $providers as $provider)
                $this->add($provider);

        }
    }

    /**
     * @param $provider
     *
     * @return void
     */
    function registerServiceProvider($provider)
    {
        if ( ! $this->configured)
        {
            $this->loadConfiguration();
        }

        if (array_key_exists($provider, $this->providers) and ! $this->isRegistered($provider))
        {
            /** @var ServiceProvider $new_service */
            $new_service = new $provider($this->di);
            $new_service->register();
            $this->providers[$provider]['registered'] = TRUE;
        }
    }

    /**
     * Register service provides that have already been added.
     *
     * @return $this
     */
    function registerServiceProviders()
    {
        foreach ($this->providers as $service => $is)
        {
            if ( ! $this->isRegistered($service))
                $this->registerServiceProvider($service);
        }

        return $this;
    }

}
