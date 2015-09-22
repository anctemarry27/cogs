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
    function add($abstract, $concrete = NULL);

    /**
     * Invoke
     *
     * @param  string $alias
     * @param  array  $args
     *
     * @return mixed
     */
    public function call($alias, array $args = []);

    /**
     * Get an item from the container
     *
     * @param  string $abstract
     * @param  array  $args
     *
     * @return mixed
     */
    function get($abstract, array $args = []);

    /**
     * @param string $abstract
     *
     * @return bool
     */
    function has($abstract);

    /**
     * Allows for methods to be invoked on any object that is resolved of the tyoe
     * provided
     *
     * @param  string   $type
     * @param  callable $callback
     *
     * @return \Og\Inflector|void
     */
    //public function inflector($type, callable $callback = NULL);
    
    /**
     * @param $abstract
     * @param $concrete
     *
     * @return void
     */
    function instance($abstract, $concrete);

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
    function remove($abstract);

    /**
     * @param      $abstract
     * @param null $concrete
     */
    function shared($abstract, $concrete);

    /**
     * Add a singleton definition to the container
     *
     * @param  string $abstract
     * @param  mixed  $concrete
     *
     * @return void
     */
    function singleton($abstract, $concrete);

    /**
     * A static pseudonym of get() for easy access.
     *
     * @param                 $abstract
     * @param array           $args
     *
     * @return mixed|object
     */
    static function make($abstract, array $args = []);

}
