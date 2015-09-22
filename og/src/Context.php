<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Og\Exceptions\ContextMutabilityError;
use Og\Support\Interfaces\ContainerInterface;

/**
 * Context is a dynamic data object wrangler.
 *
 * Usage in the framework requires that there be only ONE Context,
 * which means that the context should ALWAYS be resolved from the DI.
 */
class Context extends Collection implements ArrayAccess
{
    /** @var Forge */
    private $di;

    /**
     * @param Forge|ContainerInterface $di
     */
    function __construct(Forge $di)
    {
        parent::__construct();
        $this->di = $di;
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
