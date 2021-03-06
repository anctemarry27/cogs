<?php namespace Og\Support\Cogs\Collections;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Arr;
use Symfony\Component\Yaml\Yaml;

class Collection Extends ImmutableCollection
{
    /**
     * For Yaml import/export
     *
     * @var Yaml
     */
    private $yaml = NULL;

    /**
     * Collection constructor.
     */
    public function __construct()
    {
        parent::__construct([]);
        $this->yaml = new Yaml;
    }

    /**
     * A funky way to apply property values
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        $this->storage[$method] = count($arguments) > 0 ? $arguments[0] : TRUE;

        return $this;
    }

    /**
     * Export the entire collection contents to a json string.
     *
     * @param int $options
     *
     * @return string
     */
    public function exportJSON($options = 0)
    {
        return json_encode($this->storage, $options);
    }

    /**
     * Export the entire collection contents to a yaml string.
     *
     * @return string
     */
    public function exportYAML()
    {
        return $this->yaml->dump($this->storage);
    }

    /**
     * Import (merge) values from a json file into the collection by key.
     *
     * @param $key
     * @param $json
     *
     * @return mixed
     */
    public function importJSON($key, $json)
    {
        $array = json_decode($json);
        $this->set($key, $array);

        return $array;
    }

    /**
     * Import (merge) values from a yaml file.
     *
     * @param        $key
     * @param string $yaml_string
     *
     * @return array
     */
    public function importYAML($key, $yaml_string)
    {
        $import = $this->yaml->parse($yaml_string);
        $this->set($key, $import);

        return $import;
    }

    /**
     * Merges an array of symbols with the container.
     * Note: This method strips immutable variables from the symbols array before merging.
     *
     * @param $symbols
     *
     * @return $this|void
     */
    public function merge($symbols)
    {
        $symbols = $this->normalize($symbols);

        # strip immutable symbols as they are, well, immutable.
        $symbols = $this->filter_immutables($symbols);

        if ( ! Arr::is_assoc($symbols))
            $symbols = Arr::transform_array_hash($symbols);

        $this->storage = array_merge($this->storage, $symbols);

        return $this;
    }

}
