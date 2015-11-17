<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og;

include "../boot/boot.php";

/** @var Forge $forge - $forge is defined in ../boot/boot.php */
$app = $forge->make('app');
$app->run();
