<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Dotenv\Dotenv;
use Tracy\Debugger;

include 'conveniences.php';
include 'messages.php';
include 'paths.php';

include VENDOR . 'autoload.php';

# load environment
if (file_exists(ROOT . '.env'))
{
    $dotenv = new Dotenv(ROOT);
    $dotenv->load();
}

# install Tracy if in DEBUG mode
if (getenv('DEBUG') !== 'false')
{
    Debugger::$maxDepth = 6;
    Debugger::enable(Debugger::DEVELOPMENT);
    Debugger::$showLocation = TRUE;
    # core debug utilities
    # note that debug requires that the environment has been loaded
    include 'debug.php';
}
