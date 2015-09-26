<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

namespace Og\Support;

class BladeViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var BladeView */
    public $bv;

    public function setUp()
    {
        # initial settings
        $this->bv = new BladeView(
            [
                'cache' => ROOT . 'tests/cache',
                'template_paths' => [
                    ROOT . 'tests/templates',
                ],
            ]
        );

    }

    public function test_00_BladeView_paths()
    {
        $this->bv->addViewPath(VIEWS . 'test_prepend_path');
        $this->assertEquals(VIEWS . 'test_prepend_path', $this->bv->getViewPaths()[0]);

        $this->bv->addViewPath(VIEWS . 'test_append_path', FALSE);
        $paths = $this->bv->getViewPaths();
        $this->assertEquals(VIEWS . 'test_append_path', end($paths));
    }

    public function test_00_BladeView_hasView()
    {
        $this->assertFalse($this->bv->hasView('nonexistent'));
        $this->assertTrue($this->bv->hasView('simple'));

    }

    public function test_01_BladeView_simple()
    {
        # simple render test
        $expected = file_get_contents(ROOT . 'tests/templates/test_BladeView_simple.html');
        $this->bv['content'] = 'This is a test of the Blade template engine.';
        
        $this->assertTrue($this->bv->hasView('simple'));
        $this->assertEquals($expected, $this->bv->render('simple'),
            'BladeView should render text that matches the contents of: ' . ROOT . 'tests/templates/blade_test_01.html');
    }

}
