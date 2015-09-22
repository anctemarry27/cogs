<?php namespace Og\Traits;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Interfaces\ContainerInterface;

trait WithContainer
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Get the container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set a container.
     *
     * @param  ContainerInterface $container
     *
     * @return mixed
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
