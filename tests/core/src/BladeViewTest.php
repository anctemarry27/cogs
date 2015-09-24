<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

namespace Og;

class BladeViewTest extends \PHPUnit_Framework_TestCase
{
    public function test_BladeView_simple()
    {
        # initial settings
        $bv = new BladeView(
            Forge::getInstance(),
            [
                'cache' => ROOT . 'tests/cache/',
                'template_paths' => [
                    ROOT . 'tests/templates/',
                ],
            ]
        );

        # simple render test
        $expected = file_get_contents(ROOT . 'tests/templates/test_BladeView_simple.html');
        $this->assertEquals($expected, $bv->render('simple', ['content' => 'This is a test of the Blade template engine.']),
            'BladeView should render text that matches the contents of: '. ROOT . 'tests/templates/blade_test_01.html');
    }

}
