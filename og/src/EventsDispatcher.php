<?php namespace Og;

/**
 * The Event Manager/Dispatcher
 * Based heavily on the illuminate/events/Dispatcher by Taylor Otwell.
 *
 * Someone may wonder why I do this, even though the class implements the
 * illuminate interface. Well, because the illuminate\events package
 * carries more baggage than I'm willing to accept.
 *
 * @package     Og
 * @version     0.1.0
 * @originator  Taylor Otwell
 */

use Illuminate\Contracts\Events\Dispatcher as IlluminateEventsDispatcherInterface;
use Og\Support\Str;

final class EventsDispatcher implements IlluminateEventsDispatcherInterface
{
    /**
     * @var Forge
     */
    private $di;

    /**
     * The event firing stack.
     *
     * @var array
     */
    private $firing = [];

    /**
     * The registered event listeners.
     *
     * @var array
     */
    private $listeners = [];

    /**
     * The sorted event listeners.
     *
     * @var array
     */
    private $sorted = [];

    /**
     * The wildcard listeners.
     *
     * @var array
     */
    private $wildcards = [];

    /** @var null | static */
    static private $instance = NULL;

    /**
     *  There can be only one Og\Event Dispatcher.
     *  There can be many Emitters, however.
     *
     * @param Forge $container
     */
    function __construct(Forge $container)
    {
        $this->di = $container;

        if (static::$instance)
            return static::$instance;
        else
            static::$instance = $this;

        return static::$instance;
    }

    /**
     * @param      $name
     * @param      $callback
     * @param null $priority
     *
     * @return $this
     */
    function add($name, $callback, $priority = NULL)
    {
        if ( ! $priority)
            $this->listen($name, $callback);
        else
            $this->listen($name, $callback, $priority);

        return $this;
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string $event
     * @param  mixed  $payload
     * @param  bool   $halt
     *
     * @return array|null
     */
    function fire($event, $payload = [], $halt = FALSE)
    {
        $responses = [];

        // If an array is not given to us as the payload, we will turn it into one so
        // we can easily use call_user_func_array on the listeners, passing in the
        // payload to each of them so that they receive each of these arguments.
        if ( ! is_array($payload))
            $payload = [$payload];

        $this->firing[] = $event;

        foreach ($this->getListeners($event) as $listener)
        {
            $response = call_user_func_array($listener, $payload);

            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
            if ( ! is_null($response) && $halt)
            {
                array_pop($this->firing);

                return $response;
            }

            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if ($response === FALSE)
                break;

            $responses[] = $response;
        }

        array_pop($this->firing);

        return $halt ? NULL : $responses;
    }

    /**
     * Get the event that is currently firing.
     *
     * @return string
     */
    function firing()
    {
        return end($this->firing);
    }

    /**
     * Flush a set of queued events.
     *
     * @param  string $event
     *
     * @return void
     */
    function flush($event)
    {
        $this->fire($event . '_queue');
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string $event
     *
     * @return void
     */
    function forget($event)
    {
        unset($this->listeners[$event], $this->sorted[$event]);
    }

    /**
     * Forget all of the queued listeners.
     *
     * @return void
     */
    public function forgetPushed()
    {
        foreach ($this->listeners as $key => $value)
        {
            if (Str::endsWith($key, '_pushed'))
            {
                $this->forget($key);
            }
        }
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string $eventName
     *
     * @return bool
     */
    function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]);
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  string|array $events
     * @param  mixed        $listener
     * @param  int          $priority
     *
     * @return void
     */
    function listen($events, $listener, $priority = 0)
    {
        foreach ((array) $events as $event)
        {
            if (Str::has('*', $event))
            {
                $this->setup_wildcard_listen($event, $listener);
            }
            else
            {
                $this->listeners[$event][$priority][] = $this->make($listener);

                unset($this->sorted[$event]);
            }
        }
    }

    /**
     * Register an event and payload to be fired later.
     *
     * @param  string $event
     * @param  array  $payload
     *
     * @return void
     */
    public function push($event, $payload = [])
    {
        $this->listen($event . '_pushed', function () use ($event, $payload)
        {
            $this->fire($event, $payload);
        });
    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  string $subscriber
     *
     * @return void
     */
    function subscribe($subscriber)
    {
        $subscriber = $this->resolve_subscriber($subscriber);

        $subscriber->subscribe($this);
    }

    /**
     * Fire an event until the first non-null response is returned.
     *
     * @param  string $event
     * @param  array  $payload
     *
     * @return mixed
     */
    function until($event, $payload = [])
    {
        return $this->fire($event, $payload, TRUE);
    }

    /**
     * Forget all of the queued listeners.
     *
     * @return void
     */
    function forgetQueued()
    {
        foreach ($this->listeners as $key => $value)
        {
            if (Str::endsWith('_queue', $key))
                $this->forget($key);
        }
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param  string $eventName
     *
     * @return array
     */
    function getListeners($eventName)
    {
        $wildcards = $this->get_wildcard_listeners($eventName);

        if ( ! isset($this->sorted[$eventName]))
        {
            $this->sort($eventName);
        }

        return array_merge($this->sorted[$eventName], $wildcards);
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  mixed $listener
     *
     * @return mixed
     */
    function make($listener)
    {
        if (is_string($listener))
        {
            $listener = $this->create_class_listener($listener);
        }

        return $listener;
    }

    /**
     * Notify listeners of event
     *
     * @param string $event_name
     * @param array  $parameters
     * @param bool   $halt
     *
     * @return array|null
     */
    function notify($event_name, $parameters = [], $halt = FALSE)
    {
        return $this->fire($event_name, $parameters, $halt);
    }

    /**
     * Register an event
     * Note: Closure form should be `function ($event, $param) () { <implementation> }`
     *
     * @param string   $event_name
     * @param callable $event
     * @param int      $priority
     */
    function on($event_name, callable $event, $priority = 100)
    {
        $this->add($event_name, $event, $priority);
    }

    /**
     * Register a queued event and payload.
     *
     * @param  string $event
     * @param  array  $payload
     *
     * @return void
     */
    function queue($event, $payload = [])
    {
        $this->listen($event . '_queue', function () use ($event, $payload)
        {
            $this->fire($event, $payload);
        });
    }

    /**
     * @return null|EventsDispatcher
     */
    static function getInstance()
    {
        return static::$instance ?: new static(Forge::getInstance());
    }

    /**
     * Create a class based listener using the IoC container.
     *
     * @param  mixed $listener
     *
     * @return \Closure
     */
    private function create_class_listener($listener)
    {
        $container = $this->di;

        return function () use ($listener, $container)
        {
            // If the listener has an @ sign, we will assume it is being used to delimit
            // the class name from the handle method name. This allows for handlers
            // to run multiple handler methods in a single class for convenience.
            $segments = explode('@', $listener);

            $method = count($segments) == 2 ? $segments[1] : 'handle';

            $callable = [$container->make($segments[0]), $method];

            // We will make a callable of the listener instance and a method that should
            // be called on that instance, then we will pass in the arguments that we
            // received in this method into this listener class instance's methods.
            $data = func_get_args();

            return call_user_func_array($callable, $data);
        };
    }

    /**
     * Get the wildcard listeners for the event.
     *
     * @param  string $eventName
     *
     * @return array
     */
    private function get_wildcard_listeners($eventName)
    {
        $wildcards = [];

        foreach ($this->wildcards as $wildcard => $listeners)
        {
            if (Str::pattern_matches($wildcard, $eventName))
                $wildcards = array_merge($wildcards, $listeners);
        }

        return $wildcards;
    }

    /**
     * Resolve the subscriber instance.
     *
     * @param  mixed $subscriber
     *
     * @return mixed
     */
    private function resolve_subscriber($subscriber)
    {
        if (is_string($subscriber))
        {
            return $this->di->make($subscriber);
        }

        return $subscriber;
    }

    /**
     * Setup a wildcard listener callback.
     *
     * @param  string $event
     * @param  mixed  $listener
     *
     * @return void
     */
    private function setup_wildcard_listen($event, $listener)
    {
        $this->wildcards[$event][] = $this->make($listener);
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param  string $eventName
     *
     * @return array
     */
    private function sort($eventName)
    {
        $this->sorted[$eventName] = [];

        // If listeners exist for the given event, we will sort them by the priority
        // so that we can call them in the correct order. We will cache off these
        // sorted event listeners so we do not have to re-sort on every event.
        if (isset($this->listeners[$eventName]))
        {
            krsort($this->listeners[$eventName]);
            $this->sorted[$eventName] = call_user_func_array('array_merge', $this->listeners[$eventName]);
        }
    }
}
