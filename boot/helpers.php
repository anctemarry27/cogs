<?php

/**
 * Globally accessible convenience functions.
 *
 * @note    Please DO NOT USE THESE INDISCRIMINATELY!
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
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Stratigility\Http\Request;

if (PHP_VERSION_ID <= 50610)
{
    echo("COGS requires PHP versions >= 5.6.10.");
    exit(1);
}

if ( ! function_exists('memoize'))
{
    /**
     * Cache repeated function results.
     *
     * @param $lambda - the function whose results we cache.
     *
     * @return Closure
     */
    function memoize($lambda)
    {
        return function () use ($lambda)
        {
            # results cache
            static $results = [];

            # collect arguments and serialize the key
            $args = func_get_args();
            $key = serialize($args);

            # if the key result is not cached then cache it
            if (empty($results[$key]))
                $results[$key] = call_user_func_array($lambda, $args);

            return $results[$key];
        };
    }
}

if ( ! function_exists('partial'))
{
    /**
     * Curry a function.
     *
     * @param $lambda - the function to curry.
     * @param $arg    - the first or only argument
     *
     * @return Closure
     */
    function partial($lambda, $arg)
    {
        $func_args = func_get_args();
        $args = array_slice($func_args, 1);

        return function () use ($lambda, $args)
        {
            $full_args = array_merge($args, func_get_args());

            return call_user_func_array($lambda, $full_args);
        };
    }
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

        return empty($alias) ? $forge : $forge->get($alias);
    }
}
else throw new Exception('Cannot exclusively define COGS global forge() function.');

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

if ( ! function_exists('dlog'))
{
    /**
     * @param        $message
     * @param string $priority
     */
    function dlog($message, $priority = 'info')
    {
        if (getenv('DEBUG') === 'true')
            forge('logger')->log($message, $priority);
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
        return func_num_args() === 0
            ? forge('response')
            : forge('response')->write($content)->withStatus($status);
    }
}

if ( ! function_exists('response_body'))
{
    /**
     * Retrieve the current contents of the Response body.
     *
     * @param ResponseInterface $stream
     *
     * @return string
     */
    function response_body($stream)
    {
        # accept either a Body or a Response for stream.
        $stream = $stream instanceof ResponseInterface ? $stream->getBody() : $stream;

        # use the current stream cursor as a length or remainder.
        $stream_len = $stream->tell();

        # if the stream is empty then return an empty string.
        if ($stream_len < 1)
            return '';

        $stream->rewind();

        # return the string contents of the stream.
        return $stream->getContents();
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
include SUPPORT . "illuminate/support/helpers.php";
