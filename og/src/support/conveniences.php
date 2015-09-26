<?php

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Application;
use Og\Forge;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Stratigility\Http\Request;

if ( ! function_exists('app'))
{
    /**
     * @param null $abstract
     *
     * @return mixed|Forge
     */
    function app($abstract = null)
    {
        return $abstract ? Forge::make($abstract) : Forge::getInstance();
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
        $config = $config ?: di('config');

        return empty($key) ? $config : $config[$key];
    }
}

if ( ! function_exists('di'))
{
    /**
     * @param $alias
     *
     * @return Forge|object
     */
    function di($alias = '')
    {
        static $di;
        $di = $di ?: Forge::getInstance();

        return empty($alias) ? $di : $di[$alias];
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
        return di('events');
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
        $paths = $paths ?: di('paths');

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
        return new Request(di('server')->{'request'});
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
            ? di('request')->getAttributes()
            : di('request')->getAttribute($key, $default);
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
            return di('response');

        return di('response')->write($content)->withStatus($status);
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
