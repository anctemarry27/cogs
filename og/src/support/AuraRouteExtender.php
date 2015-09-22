<?php namespace Og\Support;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Aura\Router\Route;

/**
 * The AuraRouteExtender extends Aura\Router\Route to provide access to protected properties
 * and to add other functionality.
 *
 * @package Radium Codex
 */
class AuraRouteExtender extends Route
{
    /** @var Route */
    static protected $instance = NULL;

    /**
     * A method to return the Route method.
     *
     * @return array
     */
    public function method()
    {
        return static::$instance->method;
    }

    /**
     * A static method to return the Route method.
     *
     * @return array
     * @throws RouteInstanceError
     */
    public static function getMethod()
    {
        if ( ! static::$instance)
            throw new RouteInstanceError('Route instance not set.', E_RECOVERABLE_ERROR);

        return static::$instance->method;
    }

    /**
     * Set the target Route.
     *
     * @param Route $route
     */
    public static function setRoute(Route $route)
    {
        static::$instance = $route;
    }

}

#@formatter:off
    class RouteInstanceError extends \Exception {} 
