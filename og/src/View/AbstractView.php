<?php namespace Og\View;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Illuminate\Container\Container;
use Og\Context;
use Og\Events;
use Og\Forge;
use Og\Interfaces\ContainerInterface;

abstract class AbstractView extends Context implements ViewInterface, ArrayAccess, Renderable
{
    /** @var ContainerInterface|Forge - the forge, mainly */
    protected $di;

    /** @var Events - the COGS event dispatcher */
    protected $events;

    /** @var array - a list of paths to search for the template file */
    protected $template_paths = [];

    /**
     * Append or Prepend (default) a path to the BladeView template_paths setting.
     *
     * @param string $path    : path to append to the template_path setting
     * @param bool   $prepend : TRUE to push the new path on the top, FALSE to append
     */
    public function addViewPath($path, $prepend = TRUE)
    {
        // TODO: Implement add_template_path() method.
    }

    /**
     * Collect content from the shared View Context (etc.)
     *
     * @param array $merge_data
     *
     * @return mixed
     */
    public function collectContext($merge_data = [])
    {
        // TODO: Implement collectContext() method.
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        // TODO: Implement getContainer() method.
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function hasView($template)
    {
        // TODO: Implement has_view() method.
    }

    /**
     * Register shared dependencies.
     *
     * @return void
     */
    public function registerDependencies()
    {
        // TODO: Implement register_dependencies() method.
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
