<?php namespace Og\Exceptions;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class ContextMutabilityError extends \Exception
{
    public function __construct($key)
    {
        $message = "Context Mutability Violation: the key \"$key\" is set to read_only.";
        parent::__construct($message);
    }
}
