<?php namespace Og\Interfaces;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface ContainerInterface
{
    /**
     * Add a definition to the container
     *
     * @param string $abstract
     * @param mixed  $concrete
     *
     * @return
     */
    public function add($abstract, $concrete = NULL);

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * Note: Uses the illuminate/container `call` method.
     *
     * @param  callable|string $callback
     * @param  array           $args
     *
     * @return mixed
     */
    public function call($callback, array $args = []);

    /**
     * Get an item from the container
     *
     * @param  string $abstract
     * @param  array  $args
     *
     * @return mixed
     */
    public function get($abstract, array $args = []);

    /**
     * @param string $abstract
     *
     * @return bool
     */
    public function has($abstract);

    /**
     * @param $abstract
     * @param $concrete
     *
     * @return void
     */
    public function instance($abstract, $concrete);

    /**
     * Check if an item is being managed as a singleton
     *
     * @param  string $alias
     *
     * @return boolean
     */
    public function isSingleton($alias);

    /**
     * Remove an entry from the DI
     *
     * @param $abstract
     *
     * @return void
     */
    public function remove($abstract);

    /**
     * @param \Closure $closure
     */
    public function share(\Closure $closure);

    /**
     * @param      $abstract
     * @param null $concrete
     */
    public function shared($abstract, $concrete);

    /**
     * Add a singleton definition to the container
     *
     * @param  string $abstract
     * @param  mixed  $concrete
     *
     * @return void
     */
    public function singleton($abstract, $concrete);

    /**
     * A static pseudonym of get() for easy access.
     *
     * @param                 $abstract
     * @param array           $args
     *
     * @return mixed|object
     */
    public static function make($abstract, array $args = []);

}
