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
        parent::__construct();
    }

    /**
     * Renders a Blade template with passed and stored symbol data.
     *
     * @param string $view - view name i.e.: 'sample' resolves to [template_path]/sample.blade.php
     * @param array  $data
     *
     * @return string
     */
    public function render($view, $data = [])
    {
        // TODO: Implement render() method.
    }
}
