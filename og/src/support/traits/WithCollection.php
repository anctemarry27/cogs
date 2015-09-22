<?php namespace Og\Support\Traits;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Collection;

/**
 * WithCollection Trait
 * This traits encapsulates a collection into the host class. The host class
 * **MUST** set `$this->collection` as a dependency.
 *
 * @see the `Og\Config` class for an example.
 */
trait WithCollection
{
    /** @var Collection */
    protected $collection = NULL;

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param  string $key
     *
     * @return mixed
     */
    function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param $key
     *
     * @return array|mixed|null|object|string
     */
    function get($key)
    {
        return $this->offsetGet($key);
    }


    //function __set($key, $value)
    //{
    //    $this->offsetSet($key, $value);
    //}
    //

    /**
     * @param $key
     *
     * @return bool
     */
    function has($key)
    {
        return $this->collection->has($key);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    function offsetExists($offset)
    {
        return $this->collection->has($offset);
    }

    /**
     * @param mixed $offset
     *
     * @return array|mixed|null|object|string
     */
    function offsetGet($offset)
    {
        return $this->collection->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    function offsetSet($offset, $value)
    {
        $this->collection->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    function offsetUnset($offset)
    {
        $this->collection->forget($offset);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this|null
     */
    function set($key, $value)
    {
        return $this->collection->set($key, $value);
    }

}
