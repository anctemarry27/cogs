<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Og\Support\Str;

/**
 * Change Log:
 *      20150824 - Removed extend of Collection and encapsulated it.
 */
final class Paths extends Config implements ArrayAccess
{
    /**
     * Adds a new path to the collection.
     *
     * @param string $key
     * @param string $path
     *
     * @return $this|null
     */
    function add($key, $path)
    {
        return $this->set($key, $path);
    }

    /**
     * @param array $import
     *
     * @return $this|void
     */
    function merge($import)
    {
        # set normalize paths
        array_map(
            function ($key, $path) use (&$import)
            {
                $this->set($key, Str::normalize_path($path));
            },
            array_keys($import), array_values($import)
        );
    }

}
