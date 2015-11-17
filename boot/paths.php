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
    define('CONFIG', ROOT . 'config/');
    define('BOOT', ROOT . 'boot/');
    define('CORE', ROOT . 'og/src/');
    define('HTTP', APP . 'http/');
    define('KERNEL', ROOT . 'og/config/');
    define('VENDOR', ROOT . 'vendor/');
    define('VIEWS', APP . 'resources/views/');
    define('STORAGE', ROOT . 'local/storage/');
    define('LOCAL_CACHE', ROOT . 'local/cache/');
    define('LOCAL_LOGS', ROOT . 'local/logs/');
    define('SUPPORT', ROOT . 'og/Support/');
}

return [
    'app'     => APP,
    'boot'    => BOOT,
    'cache'   => LOCAL_CACHE,
    'config'  => CONFIG,
    'core'    => CORE,
    'http'    => HTTP,
    'kernel'  => KERNEL,
    'logs'    => LOCAL_LOGS,
    'root'    => ROOT,
    'storage' => STORAGE,
    'support' => SUPPORT,
    'vendor'  => VENDOR,
    'views'   => VIEWS,
];
