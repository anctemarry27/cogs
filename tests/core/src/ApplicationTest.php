<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Kernel\Kernel;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function test01()
    {
        $app = new Application(new Kernel(Forge::getInstance()));
        //$this->assertEquals($app, app('app'));
    }

}
