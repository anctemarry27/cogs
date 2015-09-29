<?php namespace Og\Providers;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Forge;

abstract class ServiceProvider
{
    /** @var Forge */
    protected $container;

    /** @var array */
    protected $provides = [];

    public function __construct()
    {
        $this->container = di();
    }

    /**
     * This method allows for initializing properties, methods and objects
     * that may not exist at the time the provider is registered but is required
     * for correct operation.
     *
     * @return void
     */
    public function boot()
    {
        // by default - do nothing
    }

    /**
     * @param  string $alias
     *
     * @return boolean|array
     */
    public function provides($alias = NULL)
    {
        return $alias ? array_key_exists($alias, $this->provides) : FALSE;
    }

    /**
     * Use the register method to register items with the container via the
     * protected `$this->di` property or the `getContainer` method
     * from trait `WithContainer`.
     *
     * @return void
     */
    abstract public function register();
}
