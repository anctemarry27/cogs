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
include TEST_PATH . 'boot/helpers.php';
include TEST_PATH . 'boot/messages.php';
include TEST_PATH . 'vendor/autoload.php';

# load environment
if (file_exists(TEST_PATH . 'tests/.env'))
{
    $dotenv = new Dotenv(TEST_PATH);
    $dotenv->load();
}

include BOOT . 'debug.php';

//new Application(new Kernel(Forge::getInstance()));
new Kernel(Forge::getInstance());
