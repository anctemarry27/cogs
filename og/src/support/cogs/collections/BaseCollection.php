<?php namespace Og\Support\Cogs\Collections;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Arr;

abstract class BaseCollection implements CollectionInterface, \ArrayAccess, \JsonSerializable, \IteratorAggregate
{
    # add extended capabilities through a trait
    use ArrayUtilities;

    /** @var array */
    protected $storage = [];

    /**
     * BaseCollection constructor.
     *
     * @param null $collection
     */
    public function __construct($collection = NULL)
    {
        /** @var BaseCollection collection */
        $this->storage = $collection instanceof BaseCollection
            # copy collection values
            ? $collection->copy()
            # but if an array...
            : is_array($collection)
                # return the array
                ? $collection
                # else return an empty array
                : [];
    }

    /**
     * Return a copy of the collection contents.
     *
     * @return array
     */
    function copy()
    {
        $copy = [];

        return array_merge_recursive($copy, $this->storage);
    }

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
        $this->storage[$key] = $value;
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
        return Arr::query($query, $this->storage, $default);
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
        return isset($this->storage[$key]);
    }

    /*    
     * Get the instance as an array.
     *
     * @return array
     */

    /**
     * Dynamically unset an attribute.
     *
     * @param  string $key
     *
     * @return void
     */
    function __unset($key)
    {
        unset($this->storage[$key]);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->storage;
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
     * TRUE if an indexed value exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    function has($key)
    {
        return ! is_null(Arr::query($key, $this->storage));
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

            return $this->search([$key => $value]);
        }

        return NULL;
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
     */
    function offsetUnset($offset)
    {
        Arr::forget($offset, $this->storage);
    }

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
     * @return \ArrayIterator
     */
    function getIterator()
    {
        return new \ArrayIterator($this->storage);
    }

    /**
     * @param array $collection
     *
     * @return static
     */
    function make(array $collection = [])
    {
        $new = new static;
        $new->merge($collection);

        return $new;
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

        $this->storage = array_merge($this->storage, $symbols);

        return $this;
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
            $value_set = $value_set->storage;

        elseif (is_object($value_set))
            $value_set = (array) get_object_vars($value_set);

        return $value_set;
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
     * Returns TRUE is there are any stored properties.
     *
     * @return bool
     */
    function any()
    {
        return $this->count() > 0;
    }

    /**
     * Count the number of base properties.
     *
     * @return int
     */
    function count()
    {
        return count($this->storage, COUNT_NORMAL);
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
     * @param array $new_contents
     */
    function replace(array $new_contents)
    {
        $this->storage = [];
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
        return sizeof($this->storage);
    }

    /**
     * @return array
     */
    function toArray()
    {
        return $this->storage;
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
            : $this->storage[$name] = $value;
    }

}
