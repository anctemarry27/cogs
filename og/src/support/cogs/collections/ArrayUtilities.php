<?php namespace Og\Support\Cogs\Collections;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Arr;

/**
 * trait ArrayUtilities
 *
 * Mainly used to extend BaseContainer but useful for adding
 * array-based utility methods to a class.
 *
 */
trait ArrayUtilities
{

    /**
     * Collapse the collection of items into a single array.
     *
     * @return static
     */
    public function collapse()
    {
        return new static(Arr::collapse($this->storage));
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

        return in_array($key, $this->storage);
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
            return count($this->storage) > 0 ? reset($this->storage) : NULL;
        }

        return Arr::first($this->storage, $callback, $default);
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
        return new static(array_diff($this->storage, $array));
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
        foreach ($this->storage as $key => $item)
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

        foreach ($this->storage as $key => $item)
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
            return new static(array_filter($this->storage, $callback));
        }

        return new static(array_filter($this->storage));
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->storage;
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->storage));
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
        array_map($callback, array_keys($this->storage), array_values($this->storage));

        return $this;
    }

    /**
     * Locate a value by `$key`, return `$default` if not found.
     *
     * @param string|array $key
     * @param mixed        $default
     *
     * @return mixed
     */
    function search($key, $default = NULL)
    {
        return Arr::search($key, $this->storage, $default);
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
        return json_encode($this->storage, $options);
    }
}
