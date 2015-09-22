<?php namespace Og\Exceptions;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class CollectionMutabilityError extends \LogicException
{
    public function __construct($key)
    {
        parent::__construct(
            sprintf(
                'Mutability Violation: the key "%s" is set to read_only.',
                $key
            )
        );
    }

}
