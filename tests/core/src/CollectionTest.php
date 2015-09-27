<?php namespace Og\Support\Cogs\Collections;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Arr;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class CollectionTest extends PHPUnit_Framework_TestCase
{
    /** @var Collection */
    public static $collection;

    public function test0_Validate()
    {
        $collection = new Collection(new Yaml);
        $collection->merge(static::$collection);

        $collection->thaw('part1');
        $this->assertFalse($collection->immutable('part1'));

        $collection->thaw('*');
        $this->assertFalse($collection->none());
        $this->assertTrue($collection->size() == 1);

        //$collection = new Collection(new Yaml);
        $collection->{'thing'}('boing');
        $this->assertEquals($collection['thing'], 'boing');

        $collection->freeze("*");
        $collection->freeze(['thing']);
    }

    /**
     * @expectedException \Og\Exceptions\CollectionMutabilityError
     */
    public function test10_CollectionMutabilityError()
    {
        static::$collection->freeze('json');
        static::$collection->set('json', ['a' => 'b']);
    }

    public function test1_Queries()
    {
        $this->assertEquals(TRUE, static::$collection->any());
        $this->assertEquals('stuff', static::$collection->{'part1.item1'});

        static::$collection['set'] = ['test' => 'set test'];
        $this->assertEquals(static::$collection->get('set'), ['test' => 'set test']);

        $this->assertEquals(NULL, static::$collection->set(999, 'set test'));
        $this->assertEquals('default', static::$collection->locate('does-not-exist', 'default'));

        $this->assertEquals(['test' => 'set test'], static::$collection->locate('set', 'default'));

        $this->assertEquals(static::$collection->locate('set.test'), 'set test');
        static::$collection->forget('set');
        $this->assertTrue(! static::$collection->has('set'));

    }

    public function test2_Conversions()
    {
        # conversions
        $this->assertEquals('{"part1":{"item1":"stuff"}}', static::$collection->exportJSON());
        $this->assertEquals("part1:\n    item1: stuff\n", static::$collection->exportYAML());
    }

    public function test3_Appending()
    {
        $collection = new Collection(new Yaml);
        $collection_of_values = new Collection(new Yaml);

        # appending
        static::$collection->append('part1.item2', ['name' => 'Janice']);
        $this->assertEquals('Janice', static::$collection->get('part1.item2.name'));

        static::$collection->forget('part1');
        static::$collection->append('part1', ['item2' => ['name' => 'Janice']]);

        static::$collection->with('with', 'with test');
        $this->assertEquals('with test', static::$collection->get('with'));
        $this->assertTrue(static::$collection->count() === 2);
        static::$collection->forget('with');
        $this->assertTrue(static::$collection->count() === 1);

        # setup
        $test_array = ['merge_with' => ['merge' => 'this']];
        $collection_of_values->with($test_array);

        # test with an array
        $collection->merge($test_array);
        $this->assertEquals($collection->get('merge_with.merge'), 'this');
        $collection->forget('merge_with');

        # test with a collection as a value object
        $collection->merge($collection_of_values);
        $this->assertEquals($collection->get('merge_with.merge'), 'this');
        $collection->forget('merge_with');

        # test with value object
        $value_object = new \stdClass();
        $value_object->merge_with = $test_array['merge_with'];
        $collection->merge($value_object);
        $this->assertEquals($collection->get('merge_with.merge'), 'this');

        # test with flat array
        $collection->merge(Arr::s_to_a('merge with'));
        $this->assertEquals($collection->get('merge'), NULL);
        $this->assertEquals($collection->get('with'), NULL);

        $this->setExpectedException('InvalidArgumentException', "Cannot append an already existing key: 'part1'");
        static::$collection->append('part1', ['item2' => ['name' => 'Mary']]);
    }

    public function test5_Importing()
    {
        # importing
        static::$collection->importYAML('yaml', "part3:\n    item1: stuff\n    item2: { name: YAML }\n");
        $this->assertTrue(static::$collection->has('yaml.part3.item2.name'));

        static::$collection->thaw('json');
        static::$collection->importJSON('json', '
            {
                "item1": "assorted",
                "item2": {
                    "place": "Downtown"
                }
            }');

        $this->assertTrue(static::$collection->has('json.item2.place'));
    }

    public function test6_Mapping()
    {
        # mapping
        static::$collection->append('part2', ['books' => ['No name', 'Perpetual change']]);

        $result = [];
        static::$collection->each(function ($key) use (&$result) { $result[] = $key; });
    }

    public function test7_Deletion()
    {
        # deletion
        static::$collection->delete('part2.item2', 'name');
        $this->assertTrue(! static::$collection->has('part2.item2.name'));
    }

    public function test8_Iteration()
    {
        # iteration
        $this->assertTrue(static::$collection->getIterator() instanceof \ArrayIterator);

        $result = [];
        foreach (static::$collection as $key => $value)
            $result[] = $key;

        sort($result);
        $this->assertEquals($result, Arr::s_to_a('json part1 part2 yaml'));
    }

    public function test9_Mutability()
    {
        # mutability
        static::$collection->thaw('json');
        static::$collection->thaw(['json']);
        $this->assertFalse(static::$collection->immutable('json'));

        static::$collection->freeze('json');
        $this->assertTrue(static::$collection->immutable('json'));
    }

    public function test_replace()
    {
        $collection = new Collection(new Yaml);
        $collection['test'] = TRUE;
        $collection->replace(['test' => FALSE]);
        $this->assertFalse((bool) $collection->get('test'));
    }

    public static function setUpBeforeClass()
    {
        static::$collection = new Collection(new Yaml);
        static::$collection->append('part1', ['item1' => 'stuff',]);
        static::$collection->freeze('*');
        static::assertTrue(static::$collection->immutable('part1'));
        static::$collection->thaw('*');
        static::assertFalse(static::$collection->immutable('part1'));
    }
}
    
