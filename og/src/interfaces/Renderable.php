<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

namespace Og\Interfaces;

interface Renderable
{
    /**
     * @param string $template
     *
     * @return bool
     */
    public function hasView($template);

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    public function render($template, $data = []);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return static
     */
    public function with($name, $value);

}
