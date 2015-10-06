<?php

/**
 * Globally accessible convenience functions.
 * 
 * @note Please DO NOT USE THESE INDISCRIMINATELY!
 *       These functions (and those appended at the end)
 *       are intended mainly for views, testing and
 *       implementation hiding when temporarily useful.
 * 
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Application;
use Og\Forge;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Stratigility\Http\Request;

if (PHP_VERSION_ID <= 50610)
{
    echo("COGS requires PHP versions >= 5.6.10.");
    exit(1);
}

if ( ! function_exists('forge'))
{
    /**
     * @param $alias
     *
     * @return Forge|object
     */
    function forge($alias = '')
    {
        static $forge;
        $forge = $forge ?: Forge::getInstance();

        return empty($alias) ? $forge : $forge[$alias];
    }
}
else throw new Exception('Cannot exclusively define COGS global app() function.');

if ( ! function_exists('app'))
{
    /**
     * @param null $abstract
     *
     * @return mixed|Application
     */
    function app($abstract = NULL)
    {
        return $abstract ? Forge::make($abstract) : Forge::make('app');
    }
}

if ( ! function_exists('config'))
{
    /**
     * @param $key
     *
     * @return mixed|\Og\Config
     */
    function config($key = '')
    {
        static $config = NULL;
        $config = $config ?: forge('config');

        return empty($key) ? $config : $config[$key];
    }
}

if ( ! function_exists('e'))
{
    /**
     * Escape HTML entities in a string.
     *
     * @param  string $value
     *
     * @return string
     */
    function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', FALSE);
    }
}

if ( ! function_exists('events'))
{
    /**
     * @return \Og\Events
     */
    function events()
    {
        return forge('events');
    }
}

if ( ! function_exists('path'))
{
    /**
     * @param $key
     *
     * @return mixed|\Og\Paths
     */
    function path($key = '')
    {
        static $paths = NULL;
        $paths = $paths ?: forge('paths');

        return empty($key) ? $paths : $paths->get($key);
    }
}

/**
 *  Returns value of a variable. Resolves closures.
 */
if ( ! function_exists('value'))
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

/**
 * Returns Request
 */
if ( ! function_exists('request'))
{
    function request()
    {
        return new Request(forge('server')->{'request'});
    }
}

if ( ! function_exists('input'))
{
    /**
     * @param null $key
     * @param null $default
     *
     * @return array|mixed
     */
    function input($key = NULL, $default = NULL)
    {
        return empty($key)
            ? forge('request')->getAttributes()
            : forge('request')->getAttribute($key, $default);
    }
}

/**
 * Returns a new response or the Response object.
 */
if ( ! function_exists('response'))
{
    function response($content = '', $status = 200)
    {
        if (func_num_args() == 0)
            return forge('response');

        return forge('response')->write($content)->withStatus($status);
    }
}

/**
 * Redirect via RedirectResponse.
 */
if ( ! function_exists('redirect'))
{
    function redirect($url = NULL, $status = 302, $headers = [])
    {
        return new RedirectResponse($url, $status, $headers);
    }
}

/**
 * Build a url from route name and parameters.
 */
if ( ! function_exists('url'))
{
    /**
     * @param $route_name
     */
    function url($route_name)
    {
        throw new \LogicException("url($route_name) is not implemented.");
    }
}

# include illuminate support helpers
# - mainly for BladeViews and other compatibilities.
# - note that this MUST follow globals.php content - not before.
include SUPPORT . "illuminate/support/helpers.php";
