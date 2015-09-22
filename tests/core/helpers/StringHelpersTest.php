<?php
    /**
     * @package Radium Codex
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */
    use Og\Support\Str;

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

            $this->assertTrue(Str::contains('this exists', 'Testing to see if this exists.'));
            $this->assertFalse(Str::contains('this does not exist', 'Testing to see if this exists.'));

            ### slug_to_title($slug)

            $this->assertEquals('Imagine That This Is A Slug', Str::slug_to_title('imagine-that-this-is-a-slug'));

            ### remove_namespace($class_name, $class_suffix = NULL)

            $this->assertEquals('Router', Str::remove_namespace(\Og\Router::class));
            $this->assertEquals('Router', Str::remove_namespace(\Og\Router::class, 'Radium'));

            ### name_from_class($class_name, $suffix_to_remove = 'Controller')

            $this->assertEquals('applicationcontroller', Str::name_from_class('Og\ApplicationController', ''));
            $this->assertEquals('application', Str::name_from_class('Og\ApplicationController', 'Controller'));
            $this->assertEquals('application', Str::name_from_class('Og\ApplicationController'));

            ### startsWith($needle, $haystack)

            $this->assertTrue(Str::startsWith('Odd', 'Odd Greg'));
            $this->assertFalse(Str::startsWith('Greg', 'Odd Greg'));

            ### endsWith($needle, $haystack)

            $this->assertTrue(Str::endsWith('Greg', 'Odd Greg'));
            $this->assertFalse(Str::endsWith('Odd', 'Odd Greg'));

            ### stripTrailing($characters, $string)

            $this->assertEquals('All_The_Things', Str::stripTrailing('_', "All_The_Things__"));
            $this->assertNotEquals('All_The_Things', Str::stripTrailing('_', "All_The_Things--"));

            ### truncate($string, $endlength = "30", $end = "...")

            $this->assertEquals('A line tha...',
                Str::truncate(
                    "A line that is in need of shortening and I ain't talking about cooking.",
                    $endlength = "10",
                    $end = "..."
                )
            );

            ### snakecase_to_heading($word, $space = ' ')

            $this->assertEquals('No Way Bob', Str::snakecase_to_heading('no_way_bob'));
            $this->assertEquals('No&nbsp;Way&nbsp;Bob', Str::snakecase_to_heading('no_way_bob', '&nbsp;'));

            ### snakecase_to_camelcase($string)

            $this->assertEquals('noWayBob', Str::snakecase_to_camelcase('no_way_bob'));

            ### camel_to_snakecase($input, $delimiter = '_')

            $this->assertEquals('not_a_chance_bob', Str::camel_to_snakecase('notAChanceBob', $delimiter = '_'));

            ### remove_quotes($string)

            $this->assertEquals('A string chock full of quotes', Str::remove_quotes('A "string" \'chock full\' of \'"quotes"\''));

            ### generate_token($length = 16)

            $this->assertTrue(Str::generate_token() !== Str::generate_token());
            # generate_token generates HEX pairs, thus a length of 10 == 20 in the result
            $this->assertTrue(strlen(Str::generate_token(10)) === 20);

            ### e($value)

            $this->assertEquals("A &#039;quote&#039; is &lt;b&gt;bold&lt;/b&gt;", Str::e("A 'quote' is <b>bold</b>"));

            ### h($string, $double_encode = TRUE)

            $this->assertEquals("&lt;a href='test'&gt;Test&lt;/a&gt;", Str::h("<a href='test'>Test</a>"));

            ### str_has($needles, $haystack) [a pseudonym for contains()

            $this->assertEquals(Str::has('Odd', 'Odd Greg'), Str::contains('Odd', 'Odd Greg'));

            ### encode_readable_json($to_convert, $indent = 0)

            //file_put_contents(__DIR__ . '/readable_jason.json', encode_readable_json([
            //    'a' => 1,
            //    'b' => 'stuff',
            //    'c' => ['d' => TRUE],
            //    'n' => NULL,
            //]));

            $readable_json = file_get_contents(__DIR__ . '/readable_jason.json');
            $this->assertEquals($readable_json, Str::encode_readable_json(
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

            $this->assertEquals($expect, Str::parse_class_name('Symfony\Component\HttpFoundation\AcceptHeader'));

            ### http_code($key, $default = NULL)

            $this->assertEquals('404 Not Found', Str::http_code(404));
            $this->assertEquals(502, Str::http_code('Bad Gateway'));
            $this->assertEquals(900, Str::http_code('Purple Rain', 900));
            
            ### file_in_path($name, Array $paths)
            
            $this->assertStringEndsWith('readable_jason.json', Str::file_in_path('readable_jason.json', [__DIR__.'/']));
            
            ### format_for_url($string)
            
            $this->assertEquals('the-balls-are-bouncy-eh', Str::format_for_url('The balls are bouncy, eh?'));

        }

    }
