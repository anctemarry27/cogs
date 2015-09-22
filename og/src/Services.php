<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Abstracts\ServiceProvider;
use Og\Support\Interfaces\ContainerInterface;
use Og\Support\Interfaces\ServiceManagerInterface;

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

        if ( ! is_string($provider))
            throw new \InvalidArgumentException('ServiceProvider requires a class name.');

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
     * @return $this
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

        return $this;
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

    /**
     * TODO - I don't think this works.
     *
     * Determines if a definition is registered via a service provider.
     *
     * @param  string $alias
     *
     * @return boolean
     */
    function isInServiceProvider($alias)
    {
        foreach ($this->providers as $provider => $state)
        {
            if ( ! $provider instanceof ServiceProvider)
                /** @var ServiceProvider $provider */
                $provider = new $provider($this->di);

            if ($provider->provides($alias))
            {
                $provider->register();

                return TRUE;
            }
        }

        return FALSE;
    }
}
