<?php
/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

namespace Og;

use Dotenv\Dotenv;
use Tracy\Debugger;

# get the root
define('TEST_PATH', dirname(__DIR__) . '/../');
define('Og\COLLECT_FUNCTIONS_AND_CLASSES', FALSE);

date_default_timezone_set('America/Vancouver');

include TEST_PATH . 'boot/paths.php';
include SUPPORT . 'conveniences.php';
include TEST_PATH . 'vendor/autoload.php';

# load environment
if (file_exists(TEST_PATH . 'tests/.env'))
{
    $dotenv = new Dotenv(TEST_PATH);
    $dotenv->load();
}

# install Tracy if in DEBUG mode
if (getenv('DEBUG') !== 'false')
{
    Debugger::$maxDepth = 6;
    Debugger::enable(Debugger::DEVELOPMENT);
    Debugger::$showLocation = TRUE;
}

# core debug utilities
# note that debug requires that the environment has been loaded
include SUPPORT . 'debug.php';

$di = new Forge;

# Core Configuration
$di->singleton(['config', Config::class], new Config);
$di->make('config')->importFolder(APP_CONFIG);

new Application($di);
