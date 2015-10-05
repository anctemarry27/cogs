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

    /** @var Forge */
    private $forge;

    /** @var array */
    private $providers = [];

    /**
     * Services constructor.
     *
     * @param ContainerInterface $forge
     */
    public function __construct(ContainerInterface $forge)
    {
        $this->forge = $forge;
    }

    /**
     * @param $provider
     *
     * @return $this
     */
    public function addAndRegister($provider)
    {
        $this->add($provider);
        $this->registerServiceProvider($provider);

        return $this;
    }

    /**
     * Adds a service provider to the container
     *
     * @param string $provider
     * @param bool   $as_dependency
     *
     * @return $this
     */
    public function add($provider, $as_dependency = FALSE)
    {
        $this->providers[$provider] = ['registered' => FALSE, 'booted' => FALSE];;

        if ($as_dependency)
            $this->forge->add([$provider, $provider]);

        return $this;
    }

    /**
     * @param $provider
     *
     * @return void
     */
    public function registerServiceProvider($provider)
    {
        if ( ! $this->isRegistered($provider))
        {
            /** @var ServiceProvider $new_service */
            $new_service = new $provider($this->forge);
            $new_service->register();
            $this->providers[$provider]['registered'] = TRUE;
        }
    }

    /**
     * @param $service_name
     *
     * @return bool
     */
    public function isRegistered($service_name)
    {
        return array_key_exists($service_name, $this->providers)
            ? $this->providers[$service_name]['registered']
            : FALSE;
    }

    /**
     * Boot all registered providers.
     */
    public function bootAll()
    {
        if ( ! $this->booted)
        {
            foreach ($this->providers as $provider => $is)
            {
                if ( ! $this->isRegistered($provider))
                    $this->registerServiceProvider($provider);

                if ( ! $this->isBooted($provider))
                    $this->forge->make($provider)->boot();
            }

            $this->booted = TRUE;
        }
    }

    /**
     * @param $service_name
     *
     * @return bool
     */
    public function isBooted($service_name)
    {
        return array_key_exists($service_name, $this->providers)
            ? $this->providers[$service_name]['booted']
            : FALSE;
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Load the Services configuration if not already loaded.
     *
     * @param array $providers
     *
     * @return void|static
     */
    public function loadConfiguration($providers)
    {
        foreach ((array) $providers as $provider)
            $this->add($provider);
    }

    /**
     * Register service provides that have already been added.
     *
     * @return $this
     */
    public function registerServiceProviders()
    {
        foreach ($this->providers as $service => $is)
            if ( ! $this->isRegistered($service))
                $this->registerServiceProvider($service);

        return $this;
    }

}
