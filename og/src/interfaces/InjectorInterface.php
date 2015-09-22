<?php namespace Og\Interfaces;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Exceptions\InjectorInstantiationError;

interface InjectorInterface
{
    /**
     * @param string   $name
     * @param callable $callback
     *
     * @return \stdClass
     */
    function addInflector($name, callable $callback = NULL);

    /**
     * Apply any active inflectors to the resolved object
     *
     * @param  object $object
     *
     * @return object
     */
    function applyInflectors($object);

    /**
     * Allows for methods to be invoked on any object that is resolved of the type
     * provided
     *
     * @param  string   $type
     * @param  callable $callback
     *
     * @return \League\Container\Inflector|void
     */
    function inflector($type, callable $callback = NULL);

    /**
     * Inspect the dependencies required by the given class and method.
     *
     * @param        $class
     *
     * @return array - returns an array containing details of the inspection.
     * @throws InjectorInstantiationError
     */
    function inspectClass($class);

    /**
     * Inspect the dependencies required by the given class and method.
     *
     * @param        $class
     * @param string $method
     * @param array  $parameters
     *
     * @return array - returns an array containing details of the inspection.
     */
    function inspectMethod($class, $method, ...$parameters);

    /**
     * @param string|callable $class
     * @param string          $method
     * @param array           $parameters
     *
     * @return array|mixed
     */
    function invoke($class, $method, ...$parameters);
}
