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

    public function __construct($collection = NULL)
    {
        /** @var BaseCollection collection */
        $this->collection = $collection instanceof BaseCollection ? $collection->copy() : $collection;
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
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->collection;
    }

    /*    
     * Get the instance as an array.
     *
     * @return array
     */

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
     * Collapse the collection of items into a single array.
     *
     * @return static
     */
    public function collapse()
    {
        return new static(Arr::collapse($this->collection));
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function contains($key, $value = NULL)
    {
        if (func_num_args() == 2)
        {
            return $this->contains(function ($k, $item) use ($key, $value)
            {
                return data_get($item, $key) == $value;
            });
        }

        if ($this->useAsCallable($key))
        {
            return ! is_null($this->first($key));
        }

        return in_array($key, $this->collection);
    }

    /**
     * Return a copy of the collection contents.
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
     * Get the items in the collection that are not present in the given items.
     *
     * @param  mixed $array
     *
     * @return static
     */
    public function diff($array)
    {
        return new static(array_diff($this->collection, $array));
    }

    /**
     * Execute a callback over each item.
     *
     * @param  callable $callback
     *
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->collection as $key => $item)
        {
            if ($callback($item, $key) === FALSE)
            {
                break;
            }
        }

        return $this;
    }

    /**
     * Create a new collection consisting of every n-th element.
     *
     * @param  int $step
     * @param  int $offset
     *
     * @return static
     */
    public function every($step, $offset = 0)
    {
        $new = [];

        $position = 0;

        foreach ($this->collection as $key => $item)
        {
            if ($position % $step === $offset)
            {
                $new[] = $item;
            }

            $position++;
        }

        return new static($new);
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null $callback
     *
     * @return static
     */
    public function filter(callable $callback = NULL)
    {
        if ($callback)
        {
            return new static(array_filter($this->collection, $callback));
        }

        return new static(array_filter($this->collection));
    }

    /**
     * Get the first item from the collection.
     *
     * @param  callable|null $callback
     * @param  mixed         $default
     *
     * @return mixed
     */
    public function first(callable $callback = NULL, $default = NULL)
    {
        if (is_null($callback))
        {
            return count($this->collection) > 0 ? reset($this->collection) : NULL;
        }

        return Arr::first($this->collection, $callback, $default);
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
     * @return \ArrayIterator
     */
    function getIterator()
    {
        return new \ArrayIterator($this->collection);
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
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->collection;
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->collection));
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
     * Do something with each base entry in the collection.
     *
     * @param callable $callback
     *
     * @return $this
     */
    function mapEach(callable $callback)
    {
        array_map($callback, array_keys($this->collection), array_values($this->collection));

        return $this;
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
     * Filter items by the given key value pair.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  bool   $strict
     *
     * @return static
     */
    public function where($key, $value, $strict = TRUE)
    {
        return $this->filter(function ($item) use ($key, $value, $strict)
        {
            return $strict ? data_get($item, $key) === $value
                : data_get($item, $key) == $value;
        });
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

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed $value
     *
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }

}

