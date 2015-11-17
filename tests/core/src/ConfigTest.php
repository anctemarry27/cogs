<?php namespace Og;

/**
     * @package Og
     * @version 0.1.0
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */

    use Og\Support\Collections\Collection;
    use Symfony\Component\Yaml\Yaml;

    /**
     * Test the framework core classes
     *
     * @backupGlobals          disabled
     * @backupStaticAttributes disabled
     */
    class ConfigTest extends \PHPUnit_Framework_TestCase
    {
        private $config;

        public function setUp()
        {
            $this->config = new Config(new Collection(new Yaml));
        }

        public function test_Config()
        {
            $config = new Config(new Collection(new Yaml)); # DI::make('config');

            $config->importArray([
                'name' => [
                    'first' => 'Julia',
                    'last' => 'Truesdell',
                    'nick' => 'Judd',
                ],
                'score' => 100,
                'title' => 'Mrs.',
                'rangers' => [
                    'Tom' => 'Space Oddity',
                    'Blair' => [
                        'Time to Die',
                        'A River Ran Through It',
                    ],
                ],
            ]);

            $this->assertFalse($config->offsetExists('oops'));
            # import any config files found in the test config folder
            $config->importFolder(__DIR__ . '/../../config/');

            # import an array
            $config->importArray(['imported' => ['this' => 'that',],]);

            $this->assertTrue($config->has('name.nick'));
            $this->assertTrue($config->has('imported.this'));
            $this->assertFalse($config->has('does-not-exist'));

            # using magic methods
            $this->assertEquals('Julia', $config->name['first']);
            $this->assertEquals('Truesdell', $config->name['last']);
            $this->assertEquals(['first' => 'Julia', 'last' => 'Truesdell', 'nick' => 'Judd',], $config->name);

            $config->set('book', ['title' => 'The Dark Tower', 'author' => 'Stephen King',]);
            $this->assertTrue($config->has('book.author'));
            $this->assertEquals('Stephen King', $config->{'book.author'});

            $this->assertNull($config->set(0, 'value'));
            $config['test'] = 'something';
            $config->offsetUnset('test');
            $this->assertFalse($config->has('test'));
        }

        public function test_importFile()
        {
            $config = new Config(new Collection);
            $path = TEST_PATH . 'tests/config/test.php';
            $config->importFile($path);

            $this->assertTrue($config->has('test.quotes'));
            $this->assertEquals("I didn't ask for this!", $config['test.quotes.Alfred'],
                'Result should be Alfred\'s quote');
        }

        public function test_make()
        {
            $config = new Config(new Collection);
            $config2 = $config->make();

            $this->assertTrue($config2 instanceof Config,
                'make() should create a new config object.');
            
        }

        public function test_export()
        {
            file_put_contents('tests/cogs.yaml', Config::createFromFolder(CONFIG)->exportYAML());
            //die_dump(Config::createFromYaml('cogs.yaml')->copy());
        }

    }
