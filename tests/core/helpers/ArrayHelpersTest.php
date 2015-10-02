<?php namespace Og;

/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Arr;

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

        $obj = Arr::to_object($this->source_array);
        $this->assertEquals($obj->Apples, $this->source_array['Apples']);

        ### object_to_array

        $array = Arr::object_to_array($obj);
        $this->assertEquals($this->source_array, $array);

        ### copy_object_to_array

        $obj2 = Arr::to_object(
            [
                'a' => 'not much',
            ]
        );
        $obj1 = Arr::to_object(
            [
                'apples'      => 10,
                'beets'       => 'nope',
                'candy.start' => 'now',
                'candy.end'   => 'never',
                'value_obj'   => $obj2,
            ]
        );
        $obj_array = Arr::copy_object($obj1);
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
            Arr::insert_before_key('Candy', $this->source_array, "Beef", ['hamburger', 'roast beef'])
        );

        ### array_except($array, $keys)

        $this->assertEquals(
            [
                'Apples' => 'One',
                'Candy'  => ['start' => 'now', 'end' => 'then'],
            ],
            Arr::except(['Beets'], $this->source_array)
        );

        ### array_pull(&$array, $key, $default = NULL)

        # copy the test array
        $worker = $this->source_array;
        # pull 'Beets' -> 2
        $this->assertEquals(2, Arr::pull("Beets", $worker, $default = FALSE));
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
            Arr::dict($this->source_array)
        );

        ### s_to_a - string to array

        $this->assertEquals(
            [
                'apples',
                'beets',
                'candy.start',
                'candy.end',
            ],
            Arr::s_to_a('apples beets candy.start candy.end')
        );

        ### s_to_aa - string to associative array

        $this->assertEquals(
            [
                'apples'      => 10,
                'beets'       => 'nope',
                'candy.start' => 'now',
                'candy.end'   => 'never',
            ],
            Arr::s_to_aa('apples:10, beets:nope, candy.start:now, candy.end:never')
        );

    }

    public function test03()
    {
        ### array_forget(&$array, $keys)

        # forget by single key
        $worker = $this->source_array;
        Arr::forget('Candy', $worker);
        $this->assertEquals(
            [
                'Apples' => 'One',
                'Beets'  => 2,
            ],
            $worker
        );
        # forget by dot path 
        $worker = $this->source_array;
        Arr::forget('Candy.start', $worker);
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
        $this->assertEquals([26, 32], Arr::extract_list('age', $records));

        ### (simple) array_make_compare_list(array $array)

        $worker = Arr::s_to_aa('name:Laura, access:Administrator');
        $this->assertEquals(
            [
                'name=`Laura`',
                'access=`Administrator`',
            ],
            Arr::make_compare_list($worker)
        );
        # empty returns null
        $this->assertNULL(Arr::make_compare_list([]));
        # list returns null on invalid array (must be associative)
        $this->assertNull(Arr::make_compare_list(['bad']));
    }

    public function test04()
    {
        ### array_fill_object($obj, $array)

        $obj = Arr::to_object(Arr::s_to_aa('name:Greg, location:Vancouver, cat:Julius'));
        $this->assertEquals(
            [
                'name'     => 'Greg',
                'location' => 'Vancouver',
                'cat'      => 'Julius',
            ],
            Arr::object_to_array($obj)
        );
        $obj = Arr::fill_object($obj, Arr::s_to_aa('need:Coffee'));
        $this->assertEquals(
            [
                'name'     => 'Greg',
                'location' => 'Vancouver',
                'cat'      => 'Julius',
                'need'     => 'Coffee',
            ],
            Arr::object_to_array($obj));

    }

    public function test05()
    {
        ### generate_object_value_hash($object, $value)

        $obj = new \stdClass();

        $this->assertEquals(
            [
                'stdClass' => Arr::s_to_aa('one:1, two:2, three:3, four:4'),
            ],
            Arr::generate_object_value_hash($obj, Arr::s_to_aa('one:1, two:2, three:3, four:4'))
        );
        # non-object returns null
        /** @noinspection PhpParamsInspection */
        $this->assertNull(Arr::generate_object_value_hash('not an object', Arr::s_to_aa('one:1, two:2, three:3, four:4')));

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
            Arr::pivot_array_on_index($worker)
        );

        ### array_get($array, $key, $default = NULL)

        $this->assertEquals('now', Arr::get('Candy.start', $this->source_array));
        $this->assertEquals('not found', Arr::get('Candy.nope', $this->source_array, 'not found'));

        ###  multi_explode(array $delimiters, $string, $trim)

        $this->assertEquals(
            [
                0 => 'This is a string',
                1 => ' Break it up',
                2 => ' Ok?',
            ],
            Arr::multi_explode('This is a string. Break it up! Ok?', ['.', '!'])
        );

        ### convert_list_to_indexed_array($array)

        $this->assertEquals(
            [
                0 => 'one',
                1 => 'two',
            ],
            Arr::convert_list_to_indexed_array(Arr::s_to_a('one two'))
        );

        ### get_array_value_safely($index, $array)

        $this->assertEquals(
            [
                'start' => 'now',
                'end'   => 'then',
            ],
            Arr::get_array_value_safely('Candy', $this->source_array)
        );
        $this->assertNull(Arr::get_array_value_safely('does-not-exist', $this->source_array));

    }

}
