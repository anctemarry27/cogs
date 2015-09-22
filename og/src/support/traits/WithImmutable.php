<?php namespace Og\Support\Traits;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

trait WithImmutable
{
    /** @var bool */
    private $frozen = FALSE;

    function freeze()
    {
        $this->frozen = TRUE;
    }

    function frozen()
    {
        return $this->frozen;
    }

    function thaw()
    {
        $this->frozen = FALSE;
    }

}
