<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Abstracts\ServiceProvider;
use Og\Interfaces\ContainerInterface;
use Og\Interfaces\ServiceManagerInterface;

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
            foreach ($this->providers as $provider => &$is)
            {
                if ( ! $is['registered'])
                    $this->registerServiceProvider($provider);

                if ($is['registered'] and ! $is['booted'])
                    $this->di->make($provider)->boot();
            }

            $this->booted = TRUE;
        }
    }

    /**
     * Load the Services configuration if not already loaded.
     */
    function loadConfiguration()
    {
        if ( ! $this->configured)
        {
            $this->configured = TRUE;
            $providers = config('core.providers');

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

        if (array_key_exists($provider, $this->providers) and ! $this->providers[$provider]['registered'])
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
            if ( ! $is['registered'])
                $this->registerServiceProvider($service);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

}
