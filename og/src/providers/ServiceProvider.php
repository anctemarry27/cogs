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
     * Inspired by the Laravel ServiceProvider API, this method is called
     * After all service providers have been registered. This method allows
     * for initializing properties, methods and objects that may not exist
     * at the time the provider is registered but is required for correct
     * operation.
     *
     * @return void
     */
    public function boot()
    {
        // by default - do nothing
    }

    /**
     * Returns a boolean if checking whether this provider provides a specific
     * service or returns an array of provided services if no argument passed.
     *
     * @param  string $alias
     *
     * @return boolean|array
     */
    public function provides($alias = NULL)
    {
        if ( ! is_null($alias))
        {
            return (in_array($alias, $this->provides));
        }

        return FALSE;
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
