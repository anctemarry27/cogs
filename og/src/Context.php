<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Og\Abstracts\ImmutableCollection;
use Og\Interfaces\ContainerInterface;

/**
 * Context is a dynamic data object wrangler.
 *
 * Usage in the framework requires that there be only ONE Context,
 * which means that the context should ALWAYS be resolved from the DI.
 */
class Context extends ImmutableCollection implements ArrayAccess
{
    protected $collection;

    /** @var Forge */
    private $di;

    /**
     * @param Forge|ContainerInterface $di
     * @param array|Context            $context
     */
    function __construct(ContainerInterface $di, $context = [])
    {
        $this->di = $di;
        $this->collection = $context instanceof Context ? $context->copy() : $context;
    }

}
