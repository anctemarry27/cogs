<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Application;
use Og\Forge;

include "../boot/boot.php";

$app = new Application(Forge::getInstance());
$app->run();
