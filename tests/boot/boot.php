<?php
/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

namespace Og;

use Dotenv\Dotenv;
use Og\Kernel\Kernel;

# get the root
define('TEST_PATH', dirname(__DIR__) . '/../');
define('Og\COLLECT_FUNCTIONS_AND_CLASSES', FALSE);

date_default_timezone_set('America/Vancouver');

include TEST_PATH . 'boot/paths.php';
include SUPPORT . 'helpers.php';
include SUPPORT . 'messages.php';
include TEST_PATH . 'vendor/autoload.php';

# load environment
if (file_exists(TEST_PATH . 'tests/.env'))
{
    $dotenv = new Dotenv(TEST_PATH);
    $dotenv->load();
}

include BOOT . 'debug.php';

$forge = Forge::getInstance();
$forge->add(['config', Config::class], Config::createFromFolder(CONFIG));

//new Application(new Kernel(Forge::getInstance()));
new Kernel(Forge::getInstance());
