<?php namespace Og\Support\Cogs\Collections;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class Input extends ImmutableCollection
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
    }
}
