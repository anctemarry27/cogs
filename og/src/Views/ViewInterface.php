<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
namespace Og\Views;

use Illuminate\Container\Container;

interface ViewInterface
{
    /**
     * Append or Prepend (default) a path to the BladeView template_paths setting.
     *
     * @param string $path    : path to append to the template_path setting
     * @param bool   $prepend : TRUE to push the new path on the top, FALSE to append
     */
    public function addViewPath($path, $prepend = TRUE);

    /**
     * Collect content from the shared View Context (etc.)
     *
     * @param array $merge_data
     *
     * @return mixed
     */
    public function collectContext($merge_data = []);

    /**
     * @return Container
     */
    public function getContainer();

    /**
     * @param string $template
     *
     * @return bool
     */
    public function hasView($template);

    /**
     * Renders a Blade template with passed and stored symbol data.
     *
     * @param string $view - view name i.e.: 'sample' resolves to [template_path]/sample.blade.php
     * @param array  $data
     *
     * @return string
     */
    public function render($view, $data = []);

    /**
     * Register shared dependencies.
     *
     * @return void
     */
    public function registerDependencies();
}
