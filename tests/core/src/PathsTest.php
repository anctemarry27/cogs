<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

namespace Og;

use Og\Support\Util;

/**
 * Test the framework core classes
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class PathsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Paths */
    private $paths;

    function setUp()
    {
        $this->paths = new Paths;
        $this->paths->merge([
            'root' => ROOT,
            'vendors' => VENDOR,
            'config' => __DIR__ . '/../../config/',
        ]);
    }

    function test_Paths()
    {
        $paths = $this->paths;

        $this->assertEquals($paths->copy(), [
            'root' => ROOT,
            'vendors' => VENDOR,
            # import_array should normalize paths in the array
            'config' => Util::strip_tail('/', realpath(__DIR__ . '/../../config/')) . '/',
        ]);

        $paths->add('goop', '/here/to/goop');
        $this->assertEquals(VENDOR, $paths['vendors']);
        $this->assertEquals(ROOT, $paths->{'root'});
        $this->assertEquals('/here/to/goop', $paths['goop']);

        $paths->set('test', 'nothing');
        $this->assertTrue($paths->has('test'));
        $this->assertNull($paths->set(0, 'value'));
        $paths['test'] = 'something';
        $paths->offsetUnset('test');
        $this->assertFalse($paths->has('test'));

    }

}
