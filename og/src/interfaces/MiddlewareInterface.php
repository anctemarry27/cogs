<?php namespace Og\Interfaces;

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
     * @param $abstract
     * @param $path
     *
     * @return
     */
    public function addPath($abstract, $path);
}
