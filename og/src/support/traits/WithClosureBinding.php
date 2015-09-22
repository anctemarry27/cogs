<?php namespace Og\Support\Traits;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

trait WithClosureBinding
{
    protected $bindings = [];

    /**
     * @param string $name
     * @param array  $arg
     *
     * @return mixed
     */
    function __call($name, $arg)
    {
        # does the call name match an internally bound method?
        if (is_callable($this->bindings[$name]))
            return is_array($arg)
                ? call_user_func_array($this->$name, $arg)
                : call_user_func($this->bindings[$name], $arg);
        else
            throw new \InvalidArgumentException("Method {$name} does not exist");
    }

    /**
     * @param string         $name
     * @param callable|mixed $value
     */
    function __set($name, $value)
    {
        if ( ! array_key_exists($name, $this->bindings))
        {
            /** @var \Closure $value */
            $this->bindings[$name] = is_callable($value) ? $value->bindTo($this, 'public') : $value;
        }
        else
            throw new \InvalidArgumentException("Dynamic Method '$name' cannot be redefined.");
    }
}
