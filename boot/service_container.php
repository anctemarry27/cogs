<?php namespace Og;

    /**
     * @package Og
     * @version 0.1.0
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */

/**
 * @param $alias
 *
 * @return Forge|object
 */
function di($alias = '')
{
    static $di;
    $di = $di ?: Forge::getInstance();

    return empty($alias) ? $di : $di[$alias];
}
