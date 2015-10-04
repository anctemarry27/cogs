<?php namespace Og\Views;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Illuminate\Container\Container as IlluminateContainer;
use Og\Context;
use Og\Events;
use Og\Forge;
use Og\Interfaces\ContainerInterface;
use Symfony\Component\Finder\Finder;

abstract class AbstractView extends Context implements ViewInterface, ArrayAccess, Renderable
{
    /** @var ContainerInterface|Forge - the forge, mainly */
    protected $forge;

    /** @var Events - the COGS event dispatcher */
    protected $events;

    /** @var IlluminateContainer - the illuminate/container/container */
    protected $illuminate_container;

    /** @var array - a list of paths to search for the template file */
    protected $template_paths = [];

    /**
     * AbstractView constructor.
     *
     * @param array $settings
     *
     */
    public function __construct($settings = [])
    {
        $this->forge = Forge::getInstance();
        parent::__construct($settings);
    }

    /**
     * Implements a value-as-method syntax.
     * ie: context->{thing}('is this') where 'thing' exists in the context.
     *
     * This funky hint-of-templates syntax implements the following:
     *
     *      blade_view->{'thing'} = 'string'
     *      blade_view->{'thing'}('string')[->...]
     *
     * @param  string $method
     * @param  array  $arguments
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        $this[$method] = count($arguments) > 0 ? $arguments[0] : TRUE;

        return $this;
    }

    /**
     * Append or Prepend (default) a path to the BladeView template_paths setting.
     *
     * @param string $path    : path to append to the template_path setting
     * @param bool   $prepend : TRUE to push the new path on the top, FALSE to append
     *
     * @return $this
     */
    public function addViewPath($path, $prepend = TRUE)
    {
        $prepend_or_append = $prepend ? 'array_unshift' : 'array_push';
        $prepend_or_append($this->template_paths, $path);

        return $this;
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
        return (array) $this->merge($merge_data)->copy();
    }

    /**
     * @return IlluminateContainer
     */
    public function getContainer()
    {
        return $this->illuminate_container;
    }

    /**
     * get the array of paths from the BladeView view finder.
     *
     * @return array
     */
    public function getViewPaths()
    {
        return $this->template_paths;
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function hasView($template)
    {
        $finder = new Finder();
        $finder->in($this->template_paths);

        return $finder->contains($template) or $finder->contains("$template.php");
    }

    /**
     * Register shared dependencies.
     *
     * @return void
     */
    public function registerDependencies()
    {
        // Implement or do not implement. There is no `try`.
    }

    /**
     * Renders a Blade template with passed and stored symbol data.
     *
     * @param string $view - view name i.e.: 'sample' resolves to [template_path]/sample.blade.php
     * @param array  $data
     *
     * @return string
     */
    abstract public function render($view, $data = []);
}
