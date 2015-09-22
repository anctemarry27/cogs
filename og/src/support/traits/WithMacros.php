<?php namespace Og\Support\Traits;

    /**
     * @package Og
     * @version 0.1.0
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */

/**
 * Cloned from Illuminate\Support\Traits\MacroableTrait.
 */
trait WithMacros
{
    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     * @throws \BadMethodCallException
     */
    function __call($method, $parameters)
    {
        return static::__callStatic($method, $parameters);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method))
        {
            return call_user_func_array(static::$macros[$method], $parameters);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * @return array
     */
    public static function getMacros()
    {
        return self::$macros;
    }

    /**
     * Checks if macro is registered
     *
     * @param  string $name
     *
     * @return boolean
     */
    public static function hasMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Register a custom macro.
     *
     * @param  string   $name
     * @param  callable $macro
     *
     * @return void
     */
    public static function macro($name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }

}
