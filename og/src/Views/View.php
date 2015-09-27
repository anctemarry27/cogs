<?php namespace Og\Views;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Og\Forge;

class View extends AbstractView implements ViewInterface, Renderable, ArrayAccess
{
    /**
     * View constructor.
     */
    public function __construct()
    {
        parent::__construct(Forge::getInstance());
    }
}
