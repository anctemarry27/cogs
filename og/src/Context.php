<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Og\Exceptions\ContextMutabilityError;
use Og\Support\Interfaces\ContainerInterface;
use Og\Support\Traits\WithEvents;
use Og\Support\Traits\WithMacros;

/**
 * Context is a dynamic data object wrangler.
 * Usage in the framework requires that there be only ONE Context,
 * which means that the context should ALWAYS be resolved from the DI.
 */
class Context extends Collection implements ArrayAccess
{
    // Context can create and execute `macros`
    use WithMacros;

    // Context can create and consume events.
    use WithEvents;

    // Context can set apply mutability conditions.
    //use WithImmutable;

    /** @var Forge */
    private $di;

    /**
     * @param Forge|ContainerInterface $di
     * @param EventsDispatcher         $events
     */
    function __construct(Forge $di, EventsDispatcher $events)
    {
        parent::__construct();
        $this->di = $di;
        static::$events = $events;
    }

    /**
     * Set a value if mutable.
     *
     * @param $key
     * @param $value
     *
     * @throws ContextMutabilityError
     */
    function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

}
