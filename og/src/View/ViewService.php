<?php namespace Og\View;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface ViewService
{

    /**
     * @param $templateDir
     *
     * @return mixed
     */
    function add_path($templateDir);

    /**
     * @param array $configuration
     *
     * @return mixed
     */
    function init(array $configuration = []);

    /**
     * Template Service Load Interface
     *
     * @param $template_file
     *
     * @return mixed
     */
    function load_template($template_file);

    /**
     * @param $templateDir
     *
     * @return mixed
     */
    function prepend_path($templateDir);

    /**
     * Template Service Render Interface
     *
     * @param $name
     * @param $variables
     *
     * @return mixed
     */
    function render($name, Array $variables);

    /**
     * Register the service
     *
     * @return mixed
     */
    public static function register();
}
