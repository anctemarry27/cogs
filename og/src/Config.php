<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Cogs\Collections\ImmutableCollection;
use Og\Support\Str;

class Config extends ImmutableCollection implements \ArrayAccess, \JsonSerializable
{

    /**
     * @param array $import
     */
    function importArray(Array $import)
    {
        array_map(
            function ($key, $value) { $this->set($key, $value); },
            array_keys($import),
            array_values($import)
        );
    }

    /**
     * @param $file
     */
    function importFile($file)
    {
        $this->import_files([$file]);
    }

    /**
     * Merge config files in the CONFIG path.
     *
     * @param $base_path
     *
     * @return static
     */
    function importFolder($base_path)
    {
        $this->import($base_path);

        return $this;
    }

    /**
     * @param array $array
     *
     * @return static
     */
    function make(array $array = [])
    {
        return new static($array);
    }

    /**
     * @param string $folder - name of folder with config files
     *
     * @return mixed
     */
    static public function createFromFolder($folder)
    {
        return (new static)->importFolder($folder);
    }

    /**
     * Glob a set of file names from a normalized path.
     *
     * @param $base_path
     *
     * @return array
     */
    private function files_from_path($base_path)
    {
        $base_path = Str::normalize_path($base_path);
        $files = glob($base_path . "*.php");

        return $files;
    }

    /**
     * Imports config files found in the specified directory.
     *
     * @param $base_path - the base path of the folder that contains config files.
     */
    private function import($base_path)
    {
        $this->import_files($this->files_from_path($base_path));
    }

    /**
     * Import configuration data from a set of files.
     *
     * @param $files
     */
    private function import_files(array $files)
    {
        foreach ($files as $config_file)
        {
            # use the base name as the config key.
            # i.e.: `config/happy.php` -> `happy`
            $config_key = basename($config_file, '.php');

            # load 
            $this->register_config($config_key, $config_file);
        }
    }

    /**
     * Register a configuration using the base name of the file.
     *
     * @param $config_key
     * @param $config_file
     */
    private function register_config($config_key, $config_file)
    {
        # include only if the root key does not exist
        if ( ! $this->has($config_key))
        {
            $import = include "$config_file";

            # only import if the config file returns an array
            if (is_array($import))
                $this->set($config_key, $import);
        }
    }
}
