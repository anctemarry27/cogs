<?php namespace Og\Views;

use Og\Support\Util;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class BladeViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var BladeView */
    public $view;

    public function setUp()
    {
        # initial settings
        $this->view = new BladeView(
            [
                'cache'          => ROOT . 'tests/cache',
                'template_paths' => [ROOT . 'tests/templates',],
            ]
        );

    }

    public function test_00_BladeView_hasView()
    {
        $this->assertFalse($this->view->hasView('nonexistent'));
        $this->assertTrue($this->view->hasView('simple'));
        $this->assertTrue($this->view->hasView('pages.home'));
        $this->assertTrue($this->view->hasView('layouts.main'));
    }

    public function test_00_BladeView_paths()
    {
        $this->view->addViewPath(VIEWS . 'test_prepend_path');
        $this->assertEquals(VIEWS . 'test_prepend_path', $this->view->getViewPaths()[0]);

        $this->view->addViewPath(VIEWS . 'test_append_path', FALSE);
        $paths = $this->view->getViewPaths();
        $this->assertEquals(VIEWS . 'test_append_path', end($paths));

    }

    public function test_01_BladeView_simple()
    {
        # simple render test
        $expected = file_get_contents(ROOT . 'tests/templates/test_BladeView_simple.html');
        $this->view['content'] = 'This is a test of the Blade template engine.';

        $this->assertEquals($expected, $this->view->render('simple'),
            'BladeView should render text that matches the contents of: ' . ROOT . 'tests/templates/blade_test_01.html');
    }

    public function test_02_BladeView_layout()
    {
        $html = $this->view->render('pages.home', ['contents' => 'This was passed at render time.']);
        $this->assertTrue(Util::string_has('<div class="container">', $html));
        $this->assertTrue(Util::string_has('This was passed at render time', $html));
    }

}
