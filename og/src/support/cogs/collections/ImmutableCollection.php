<?php namespace Og\Support\Cogs\Collections;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Exceptions\CollectionMutabilityError;
use Og\Exceptions\ContextMutabilityError;
use Og\Support\Arr;

abstract class ImmutableCollection extends BaseCollection
{
    /**
     * A simple registry of immutable properties
     *
     * @var array
     */
    protected $read_only = [];

    /**
     * Set a new value and make it immutable.
     * 
     * @param string $key
     * @param mixed  $value
     *
     * @return $this|null|ImmutableCollection|void
     */
    public function __set($key, $value)
    {
        $status = $this->set($key, $value);
        $this->lock_one($key);
        
        return $status;
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
    public function append($key, $value = NULL)
    {
        if ( ! $this->has($key) and ! $this->immutable($key))
        {
            $this->set($key, $value);

            return $value;
        }
        else
            throw new \InvalidArgumentException("Cannot append an already existing key: '$key'");
    }

    /**
     * Freezes (make immutable) a key or the entire collection.
     * Use '*' for the key to freeze it all
     *
     * @param string $key
     */
    public function freeze($key = '*')
    {
        # mark all current entries as read_only?
        if ($key === '*')
            $this->lock_all();

        elseif (is_array($key))
            $this->lock_many($key);

        else
            $this->lock_one($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function immutable($key)
    {
        return array_key_exists($key, $this->read_only) and ($this->read_only[$key]);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this|null
     * @throws ContextMutabilityError
     */
    public function set($key, $value)
    {
        if ( ! $this->mutable($key))
            throw new ContextMutabilityError("Index `$key` is immutable.");

        # attempt writing the value to the key
        if (is_string($key))
        {
            list($key, $value) = Arr::expand_notated($key, $value);

            return $this->search([$key => $value]);
        }

        return NULL;
    }

    /**
     * Thaws (make mutable) a key or the entire collection.
     * Use '*' for the key to thaw it all.
     *
     * @param string $key
     */
    public function thaw($key = '*')
    {
        # mark all current entries as read_only?
        if ($key === '*')
        {
            # empty the read_only registry
            $this->read_only = [];
        }
        else
        {
            Arr::forget($key, $this->read_only);
        }
    }

    /**
     * Remove values from an array that match entries marked as immutable in the collection.
     *
     * @param $vars
     *
     * @return mixed
     */
    protected function filter_immutables($vars)
    {
        $candidates = [];
        foreach ($vars as $key => $value)
            if (array_key_exists($key, $this->read_only))
                $candidates[] = $key;

        Arr::forget($candidates, $vars);

        return $vars;
    }

    /**
     * @param string $key
     *
     * @return null
     */
    protected function mutable($key)
    {
        # extract all of the key segments (if any)
        $path = explode('.', $key);

        # first - validate that the key|value is writable
        foreach ($path as $candidate)
            // don't set a value if the element is read-only
            if (array_key_exists($candidate, $this->read_only))
                throw new CollectionMutabilityError($key);

        return TRUE;
    }

    /**
     * @param bool|TRUE $force
     */
    private function lock_all($force = FALSE)
    {
        foreach ($this->storage as $abstract => $value)
            $this->read_only[$abstract] = ! $force;
    }

    /**
     * @param array $keys
     * @param bool  $force
     */
    private function lock_many(array $keys, $force = FALSE)
    {
        # mark a list
        foreach ($keys as $entry)
            $this->read_only[$entry] = ! $force;
    }

    /**
     * @param string $key
     * @param bool   $force - false = lock, true = unlock
     */
    private function lock_one($key, $force = FALSE)
    {
        # mark an entry
        $this->read_only[$key] = ! $force;
    }

}
