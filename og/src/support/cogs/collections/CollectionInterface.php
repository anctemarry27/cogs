<?php namespace Og\Support\Cogs\Collections;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface CollectionInterface
{
    /**
     * Returns TRUE is there are any stored properties.
     *
     * @return bool
     */
    function any();

    /**
     * Using the provided arrays.php helper, delete elements that
     * match the dot-notation index.
     *
     * @param       $dot_path
     * @param       $target_value
     */
    function delete($dot_path, $target_value);

    /**
     * Forget an element indexed with dot-notation.
     *
     * @TODO - is this any different than delete()?
     *
     * @param $key
     */
    function forget($key);

    /**
     * Using the provided arrays.php helper, get a value from the collection
     * by its dot-notated index.
     *
     * @param null $query
     * @param null $default
     *
     * @return mixed
     */
    function get($query, $default = NULL);

    /**
     * TRUE if an indexed value exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    function has($key);

    /**
     * Merges an array of symbols with the container.
     * Note: This method strips immutable variables from the symbols array before merging.
     *
     * @param $symbols
     */
    function merge($symbols);

    /**
     * @param $key
     * @param $value
     *
     * @return $this|null
     */
    function set($key, $value);

    /**
     * Merge a single key, value pair.
     *
     * @param   string $name
     * @param null     $value
     *
     * @return static
     */
    function with($name, $value = NULL);
}
