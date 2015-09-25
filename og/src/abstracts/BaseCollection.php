<?php namespace Og\Abstracts;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Interfaces\CollectionInterface;
use Og\Support\Arr;

abstract class BaseCollection implements CollectionInterface, \ArrayAccess, \JsonSerializable, \IteratorAggregate
{
    /** @var array */
    protected $collection = [];

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
     * Dynamically set the value of an attribute.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    function __set($key, $value)
    {
        $this->collection[$key] = $value;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param  string $key
     *
     * @return bool
     */
    function __isset($key)
    {
        return isset($this->collection[$key]);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param  string $key
     *
     * @return void
     */
    function __unset($key)
    {
        unset($this->collection[$key]);
    }

    /**
     * Returns TRUE is there are any stored properties.
     *
     * @return bool
     */
    function any()
    {
        return $this->count() > 0;
    }

    /*    
     * Get the instance as an array.
     *
     * @return array
     */

    /**
     * Using the provided arrays.php helper, delete elements that
     * match the dot-notation index.
     *
     * @param       $dot_path
     * @param       $target_value
     */
    function delete($dot_path, $target_value)
    {
        Arr::forget($this->get($dot_path), $target_value);
    }

    /**
     * Forget an element indexed with dot-notation.
     *
     * @TODO - is this any different than delete()?
     *
     * @param $key
     */
    function forget($key)
    {
        if (static::offsetExists($key))
        {
            $keys = explode('.', $key);
            $value = $keys[sizeof($keys) - 1];
            $this->offsetUnset($keys[sizeof($keys) - 1]);
            $key = join('.', $keys);

            static::delete($key, $value);
        }
    }

    /**
     * Using the provided arrays.php helper, get a value from the collection
     * by its dot-notated index.
     *
     * @param null $query
     * @param null $default
     *
     * @return mixed
     */
    function get($query, $default = NULL)
    {
        return Arr::query($query, $this->collection, $default);
    }

    /**
     * TRUE if an indexed value exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    function has($key)
    {
        return ! is_null(Arr::query($key, $this->collection));
    }

    /**
     * Merges an array of symbols with the container.
     * Note: This method strips immutable variables from the symbols array before merging.
     *
     * @param $symbols
     *
     * @return $this
     */
    function merge($symbols)
    {
        $symbols = $this->normalize($symbols);

        if ( ! Arr::is_assoc($symbols))
            $symbols = Arr::transform_array_hash($symbols);

        $this->collection = array_merge($this->collection, $symbols);

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this|null
     */
    function set($key, $value)
    {
        # attempt writing the value to the key
        if (is_string($key))
        {
            list($key, $value) = Arr::expand_notated($key, $value);

            return $this->locate([$key => $value]);
        }

        return NULL;
    }

    /**
     * Merge a single key, value pair.
     *
     * @param   string $name
     * @param null     $value
     *
     * @return static
     */
    function with($name, $value = NULL)
    {
        Arr::is_assoc($name)
            ? $this->merge($name)
            : $this->collection[$name] = $value;
    }

    /**
     * append adds a new key::value to an existing key.
     * Note: if $value is not a key::value then it is converted to an array.
     *
     * @param string        $key - $key may be a simple string or a dot notation string.
     * @param array | mixed $value
     *
     * @return $this
     */
    function append($key, $value = NULL)
    {
        if ( ! $this->has($key))
        {
            $this->set($key, $value);

            return $value;
        }
        else
            throw new \InvalidArgumentException("Cannot append an already existing key: '$key'");
    }

    /**
     * Return a copy of the container contents.
     *
     * @return array
     */
    function copy()
    {
        $copy = [];

        return array_merge_recursive($copy, $this->collection);
    }

    /**
     * Count the number of base properties.
     *
     * @return int
     */
    function count()
    {
        return count($this->collection, COUNT_NORMAL);
    }

    /**
     * Do something with each base entry in the collection.
     *
     * @param callable $callback
     *
     * @return $this
     */
    function each(callable $callback)
    {
        array_map($callback, array_keys($this->collection), array_values($this->collection));

        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->collection;
    }

    /**
     * Locate a value by `$key`, return `$default` if not found.
     *
     * @param string|array $key
     * @param mixed        $default
     *
     * @return mixed
     */
    function locate($key, $default = NULL)
    {
        return Arr::deep_query($this->collection, $key, $default);
    }

    /**
     * TRUE if the collection has no entries.
     *
     * @return bool
     */
    function none()
    {
        return ! $this->any();
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     *
     * @return array|mixed|null|object|string
     */
    function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    function offsetUnset($offset)
    {
        Arr::forget($offset, $this->collection);
    }

    /**
     * @param array $new_contents
     */
    function replace(array $new_contents)
    {
        $this->collection = [];
        foreach ($new_contents as $key => $value)
            $this->merge($new_contents);
    }

    /**
     * Number of base entries in the container.
     *
     * @return int
     */
    function size()
    {
        return sizeof($this->collection);
    }

    /**
     * @return array
     */
    function toArray()
    {
        return $this->collection;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    function toJson($options = 0)
    {
        return json_encode($this->collection, $options);
    }

    /**
     * Normalize value objects to array.
     *
     * @param $value_set
     *
     * @return array
     */
    protected function normalize($value_set)
    {
        if ($value_set instanceof BaseCollection)
            $value_set = $value_set->collection;

        elseif (is_object($value_set))
            $value_set = (array) get_object_vars($value_set);

        return $value_set;
    }

}

