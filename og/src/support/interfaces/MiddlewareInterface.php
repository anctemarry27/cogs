<?php namespace Og\Support\Interfaces;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface MiddlewareInterface
{
    /**
     * Queue a Middleware service by class name.
     *
     * @param $abstract
     */
    public function add($abstract);

    /**
     * Queue a Middleware service attached to a path.
     *
     * @param $path
     * @param $concrete
     */
    public function addPath($path, $concrete);
}
