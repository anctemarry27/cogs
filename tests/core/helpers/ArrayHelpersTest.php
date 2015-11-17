<?php namespace Og;

/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Util;

/**
 * Test the framework support functions
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ArrayHelpersTest extends \PHPUnit_Framework_TestCase
{
    public $source_array = [
        'Apples' => 'One',
        'Beets'  => 2,
        'Candy'  => ['start' => 'now', 'end' => 'then'],
    ];

    public $source_array_table = [
        'Name',
    ];

    public function test01()
    {
        ### array_to_object

        $obj = Util::cast_array_to_object($this->source_array);
        $this->assertEquals($obj->Apples, $this->source_array['Apples']);

        ### object_to_array

        $array = Util::cast_object_as_array($obj);
        $this->assertEquals($this->source_array, $array);

        ### copy_object_to_array

        $obj2 = Util::cast_array_to_object(
            [
                'a' => 'not much',
            ]
        );
        $obj1 = Util::cast_array_to_object(
            [
                'apples'      => 10,
                'beets'       => 'nope',
                'candy.start' => 'now',
                'candy.end'   => 'never',
                'value_obj'   => $obj2,
            ]
        );
        $obj_array = Util::array_from_object($obj1);
        $this->assertEquals(
            [
                'apples'      => 10,
                'beets'       => 'nope',
                'candy.start' => 'now',
                'candy.end'   => 'never',
                'value_obj'   => ['a' => 'not much'],
            ],
            $obj_array
        );

        ### array_insert_before_key

        $this->assertEquals(
            [
                'Apples' => 'One',
                'Beets'  => 2,
                'Beef'   => ['hamburger', 'roast beef'],
                'Candy'  => ['start' => 'now', 'end' => 'then'],
            ],
            Util::insert_before('Candy', $this->source_array, "Beef", ['hamburger', 'roast beef'])
        );

        ### array_except($array, $keys)

        $this->assertEquals(
            [
                'Apples' => 'One',
                'Candy'  => ['start' => 'now', 'end' => 'then'],
            ],
            Util::array_except(['Beets'], $this->source_array)
        );

        ### array_pull(&$array, $key, $default = NULL)

        # copy the test array
        $worker = $this->source_array;
        # pull 'Beets' -> 2
        $this->assertEquals(2, Util::array_pull("Beets", $worker, $default = FALSE));
        # verify removed from original
        $this->assertArrayNotHasKey("Beets", $worker);

    }

    public function test02()
    {
        # array_dict - array to flattened dictionary

        $this->assertEquals(
            [
                'Apples'      => 'One',
                'Beets'       => 2,
                'Candy.start' => 'now',
                'Candy.end'   => 'then',
            ],
            Util::array_flatten($this->source_array)
        );

        ### s_to_a - string to array

        $this->assertEquals(
            [
                'apples',
                'beets',
                'candy.start',
                'candy.end',
            ],
            Util::array_from_str('apples beets candy.start candy.end')
        );

        ### s_to_aa - string to associative array

        $this->assertEquals(
            [
                'apples'      => 10,
                'beets'       => 'nope',
                'candy.start' => 'now',
                'candy.end'   => 'never',
            ],
            Util::assoc_from_str('apples:10, beets:nope, candy.start:now, candy.end:never')
        );

    }

    public function test03()
    {
        ### array_forget(&$array, $keys)

        # forget by single key
        $worker = $this->source_array;
        Util::array_forget('Candy', $worker);
        $this->assertEquals(
            [
                'Apples' => 'One',
                'Beets'  => 2,
            ],
            $worker
        );
        # forget by dot path 
        $worker = $this->source_array;
        Util::array_forget('Candy.start', $worker);
        $this->assertEquals(
            [
                'Apples' => 'One',
                'Beets'  => 2,
                'Candy'  => ['end' => 'then'],
            ],
            $worker
        );

        ### array_extract_list($find_key, $array)

        $records = [
            'George' => ['age' => 26, 'gender' => 'Male'],
            'Lois'   => ['age' => 32, 'gender' => 'Female'],
        ];
        $this->assertEquals([26, 32], Util::extract_column('age', $records));

        ### (simple) array_make_compare_list(array $array)

        $worker = Util::assoc_from_str('name:Laura, access:Administrator');
        $this->assertEquals(
            [
                'name=`Laura`',
                'access=`Administrator`',
            ],
            Util::make_compare($worker)
        );
        # empty returns null
        $this->assertNull(Util::make_compare([]));
        # list returns null on invalid array (must be associative)
        $this->assertNull(Util::make_compare(['bad']));
    }

    public function test04()
    {
        ### array_fill_object($obj, $array)

        $obj = Util::cast_array_to_object(Util::assoc_from_str('name:Greg, location:Vancouver, cat:Julius'));
        $this->assertEquals(
            [
                'name'     => 'Greg',
                'location' => 'Vancouver',
                'cat'      => 'Julius',
            ],
            Util::cast_object_as_array($obj)
        );
        $obj = Util::fill_object($obj, Util::assoc_from_str('need:Coffee'));
        $this->assertEquals(
            [
                'name'     => 'Greg',
                'location' => 'Vancouver',
                'cat'      => 'Julius',
                'need'     => 'Coffee',
            ],
            Util::cast_object_as_array($obj));

    }

    public function test05()
    {
        ### generate_object_value_hash($object, $value)

        $obj = new \stdClass();

        $this->assertEquals(
            [
                'stdClass' => Util::assoc_from_str('one:1, two:2, three:3, four:4'),
            ],
            Util::value_class($obj, Util::assoc_from_str('one:1, two:2, three:3, four:4'))
        );
        # non-object returns null
        /** @noinspection PhpParamsInspection */
        $this->assertNull(Util::value_class('not an object', Util::assoc_from_str('one:1, two:2, three:3, four:4')));

        ### pivot_array_on_index(array $input)

        $worker = [
            [
                'name' => 'Google',
                'url'  => 'https://google.com',
            ],
            [
                'name' => 'Yahoo!',
                'url'  => 'http://yahoo.com',
            ],
        ];
        $this->assertEquals(
            [
                'name' =>
                    [
                        'Google',
                        'Yahoo!',
                    ],
                'url'  =>
                    [
                        'https://google.com',
                        'http://yahoo.com',
                    ],
            ],
            Util::pivot_array($worker)
        );

        ### array_get($array, $key, $default = NULL)

        $this->assertEquals('now', Util::array_get('Candy.start', $this->source_array));
        $this->assertEquals('not found', Util::array_get('Candy.nope', $this->source_array, 'not found'));

        ###  multi_explode(array $delimiters, $string, $trim)

        $this->assertEquals(
            [
                0 => 'This is a string',
                1 => ' Break it up',
                2 => ' Ok?',
            ],
            Util::multi_explode('This is a string. Break it up! Ok?', ['.', '!'])
        );

        ### convert_list_to_indexed_array($array)

        $this->assertEquals(
            [
                0 => 'one',
                1 => 'two',
            ],
            Util::array_to_numeric_index(Util::array_from_str('one two'))
        );

        ### get_array_value_safely($index, $array)

        //$this->assertEquals(
        //    [
        //        'start' => 'now',
        //        'end'   => 'then',
        //    ],
        //    Util::get_array_value_safely('Candy', $this->source_array)
        //);
        //$this->assertNull(Util::get_array_value_safely('does-not-exist', $this->source_array));

    }

    public function test06()
    {
        $searchRA = [
            'name'   => 'greg',
            'record' => [
                'age'    => 100,
                'amount' => 26.58,
                'source' => 'pension',
            ],
        ];

        $this->assertEquals('not found', Util::array_get('record.lazy', $searchRA, 'not found'));
        $this->assertEquals($searchRA['record'], Util::array_search_and_replace('record.lazy', $searchRA, 'not found'));
        $this->assertEquals(26.58, Util::array_get('record.amount', $searchRA, 'not found'));
        //$this->assertEquals('not found', Util::search('not.there', $searchRA, 'not found'));

        //die_dump([$resultSearch, $resultGet]);
    }

}
