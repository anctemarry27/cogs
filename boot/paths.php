<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

# only one may pass
if ( ! defined('ROOT'))
{
    define('ROOT', dirname(__DIR__) . '/');
    define('APP', ROOT . 'app/');
    define('BOOT', ROOT . 'boot/');
    define('VENDOR', ROOT . 'vendor/');
    define('APP_CONFIG', ROOT . 'config/');
    define('CORE', ROOT . 'og/src/');
    define('SUPPORT', CORE . 'Support/');
    define('VIEWS', APP . 'http/views/');
}

return [
    'root' => ROOT,
    'boot' => BOOT,
    'vendor' => VENDOR,
    'config' => APP_CONFIG,
    'core' => CORE,
    'http' => APP . "http/",
    'support' => SUPPORT,
];
