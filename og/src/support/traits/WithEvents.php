<?php namespace Og\Support\Traits;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\EventsDispatcher;

/**
 * Trait WithEvents
 *
 * @dependencies    DI, Events
 */
trait WithEvents
{
    /** @var EventsDispatcher - common to all host classes */
    static protected $events = NULL;

    /**
     * @param       $event
     * @param array $parameters
     * @param bool  $halt
     */
    function notify($event, $parameters = [], $halt = FALSE)
    {
        static::$events->fire($event, $parameters, $halt);
    }

    /**
     * @param string|callable $condition
     * @param string          $event
     * @param array           $parameters
     * @param bool            $halt
     */
    function notify_if($condition, $event, $parameters = [], $halt = FALSE)
    {
        if (is_callable($condition))
        {
            if (call_user_func($condition))
            {
                static::$events->fire($event, $parameters, $halt);
            };
        }
        elseif ($condition)
        {
            static::$events->fire($event, $parameters, $event);
        }
    }

    /**
     * Creates a listener called <event_name> that triggers <callable>.
     *
     * @param string|array $event_name
     * @param callable     $event
     * @param null         $priority
     *
     * @return $this
     */
    function on($event_name, $event, $priority = NULL)
    {
        static::$events->add($event_name, $event, $priority);
    }

    /**
     * @param EventsDispatcher $events
     */
    function setEventsDispatcher(EventsDispatcher $events)
    {
        static::$events = $events;
    }
}
