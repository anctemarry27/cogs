<?php

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Util;

/**
 * Test the framework support functions
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class StringHelpersTest extends \PHPUnit_Framework_TestCase
{
    public function test01()
    {
        ### contains($needles, $haystack)

        $this->assertTrue(Util::string_has('this exists', 'Testing to see if this exists.'));
        $this->assertFalse(Util::string_has('this does not exist', 'Testing to see if this exists.'));

        ### slug_to_title($slug)

        $this->assertEquals('Imagine That This Is A Slug', Util::slug_to_title('imagine-that-this-is-a-slug'));

        ### remove_namespace($class_name, $class_suffix = NULL)

        $this->assertEquals('Routing', Util::remove_namespace(\Og\Routing::class));
        $this->assertEquals('Routing', Util::remove_namespace(\Og\Routing::class, 'Og'));

        ### name_from_class($class_name, $suffix_to_remove = 'HttpController')

        $this->assertEquals('applicationcontroller', Util::alias_from_class('Og\ApplicationController', ''));
        $this->assertEquals('application', Util::alias_from_class('Og\ApplicationController', 'Controller'));
        $this->assertEquals('application', Util::alias_from_class('Og\ApplicationController'));

    }

    public function test02()
    {
        ### startsWith($needle, $haystack)

        $this->assertTrue(Util::starts_with('Odd', 'Odd Greg'));
        $this->assertFalse(Util::starts_with('Greg', 'Odd Greg'));

        ### endsWith($needle, $haystack)

        $this->assertTrue(Util::ends_with('Greg', 'Odd Greg'));
        $this->assertFalse(Util::ends_with('Odd', 'Odd Greg'));

        ### stripTrailing($characters, $string)

        $this->assertEquals('All_The_Things', Util::strip_tail('_', "All_The_Things__"));
        $this->assertNotEquals('All_The_Things', Util::strip_tail('_', "All_The_Things--"));

        ### truncate($string, $endlength = "30", $end = "...")

        $this->assertEquals('A line tha...',
            Util::truncate(
                "A line that is in need of shortening and I ain't talking about cooking.",
                $endlength = "10",
                $end = "..."
            )
        );

    }

    public function test03()
    {
        ### snakecase_to_heading($word, $space = ' ')

        $this->assertEquals('No Way Bob', Util::snake_to_text('no_way_bob'));
        $this->assertEquals('No&nbsp;Way&nbsp;Bob', Util::snake_to_text('no_way_bob', '&nbsp;'));

        ### snakecase_to_camelcase($string)

        $this->assertEquals('noWayBob', Util::snake_to_camel('no_way_bob'));

        ### camel_to_snakecase($input, $delimiter = '_')

        $this->assertEquals('not_a_chance_bob', Util::camel_to_snake('notAChanceBob', $delimiter = '_'));

    }

    public function test04()
    {
        ### remove_quotes($string)

        $this->assertEquals('A string chock full of quotes', Util::remove_quotes('A "string" \'chock full\' of \'"quotes"\''));

        ### generate_token($length = 16)

        $this->assertTrue(Util::generate_token() !== Util::generate_token());
        # generate_token generates HEX pairs, thus a length of 10 == 20 in the result
        $this->assertTrue(strlen(Util::generate_token(10)) === 20);

        ### e($value)

        $this->assertEquals("A &#039;quote&#039; is &lt;b&gt;bold&lt;/b&gt;", Util::e("A 'quote' is <b>bold</b>"));

        ### h($string, $double_encode = TRUE)

        /** @noinspection HtmlUnknownTarget */
        $this->assertEquals("&lt;a href='test'&gt;Test&lt;/a&gt;", Util::h("<a href='test'>Test</a>"));

    }

    public function test05()
    {
        ### encode_readable_json($to_convert, $indent = 0)

        //file_put_contents(__DIR__ . '/readable_jason.json', encode_readable_json([
        //    'a' => 1,
        //    'b' => 'stuff',
        //    'c' => ['d' => TRUE],
        //    'n' => NULL,
        //]));

        $readable_json = file_get_contents(__DIR__ . '/readable_jason.json');
        $this->assertEquals($readable_json, Util::encode_readable_json(
            [
                'a' => 1,
                'b' => 'stuff',
                'c' => ['d' => TRUE],
                'n' => NULL,
            ]
        )
        );

        ### parse_class_name($name)

        $expect = [
            'namespace'      =>
                [
                    0 => 'Symfony',
                    1 => 'Component',
                    2 => 'HttpFoundation',
                ],
            'class_name'     => 'AcceptHeader',
            'namespace_path' => 'Symfony\\Component\\HttpFoundation',
            'namespace_base' => 'Symfony',
        ];

        $this->assertEquals($expect, Util::parse_class_name('Symfony\Component\HttpFoundation\AcceptHeader'));
    }

    public function test06()
    {
        ### http_code($key, $default = NULL)

        $this->assertEquals('404 Not Found', Util::http_code(404));
        $this->assertEquals(502, Util::http_code('Bad Gateway'));
        $this->assertEquals(900, Util::http_code('Purple Rain', 900));

        ### file_in_path($name, Array $paths)

        $this->assertStringEndsWith('readable_jason.json', Util::file_in_path('readable_jason.json', [__DIR__ . '/']));

        ### format_for_url($string)

        $this->assertEquals('the-balls-are-bouncy-eh', Util::str_to_url('The balls are bouncy, eh?'));
    }

}
