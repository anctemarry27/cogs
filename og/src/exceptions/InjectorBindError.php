<?php namespace Og\Exceptions;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class InjectorBindError extends \Exception
{

    /**
     * InjectorBindError constructor.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
