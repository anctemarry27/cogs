<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Interfaces\ContainerInterface;
use Og\Support\Traits\WithContainer;

class Inflector
{
    use WithContainer;

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var ContainerInterface|Forge
     */
    private $di;

    /**
     * @param ContainerInterface $di
     */
    function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * Apply inflections to an object
     *
     * @param  object $object
     *
     * @return void
     */
    function inflect($object)
    {
        $properties = $this->resolveArguments(array_values($this->properties));
        $properties = array_combine(array_keys($this->properties), $properties);

        foreach ($properties as $property => $value)
        {
            $object->{$property} = $value;
        }

        foreach ($this->methods as $name => $args)
        {
            $args = $this->resolveArguments($args);

            call_user_func_array([$object, $name], $args);
        }
    }

    /**
     * Defines a method to be invoked on the subject object
     *
     * @param  string $name
     * @param  array  $args
     *
     * @return $this
     */
    function invokeMethod($name, array $args)
    {
        $this->methods[$name] = $args;

        return $this;
    }

    /**
     * Defines multiple methods to be invoked on the subject object
     *
     * @param  array $methods
     *
     * @return $this
     */
    function invokeMethods(array $methods)
    {
        foreach ($methods as $name => $args)
        {
            $this->invokeMethod($name, $args);
        }

        return $this;
    }

    /**
     * Make a new Inflector.
     *
     * @return static
     */
    function make()
    {
        return new static($this->di);
    }

    /**
     * Uses the container to resolve arguments
     *
     * @param  array $args
     *
     * @return array
     */
    function resolveArguments(array $args)
    {
        $resolved = [];

        foreach ($args as $arg)
        {
            $resolved[] = (
                is_string($arg) && (
                    $this->di->isRegistered($arg) ||
                    $this->di->isSingleton($arg) ||
                    $this->di->isInServiceProvider($arg) ||
                    class_exists($arg)
                )
            ) ? $this->di->get($arg) : $arg;
        }

        return $resolved;
    }

    /**
     * Defines multiple properties to be set on the subject object
     *
     * @param array $properties
     *
     * @return $this
     */
    function setProperties(array $properties)
    {
        foreach ($properties as $property => $value)
        {
            $this->setProperty($property, $value);
        }

        return $this;
    }

    /**
     * Defines a property to be set on the subject object
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return $this
     */
    function setProperty($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }
}
