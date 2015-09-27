<?php

    /**
     * @package Og
     * @version 0.1.0
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */

    use Illuminate\Container\Container as IlluminateContainer;
    use Og\Forge;
    use Og\Providers\TestServiceProvider;

    /**
     * Test the framework core classes
     *
     * @group                  core
     * @backupGlobals          disabled
     * @backupStaticAttributes disabled
     */
    class DITest extends PHPUnit_Framework_TestCase
    {
        /** @var Forge */
        private $di;

        /** @var \Og\Interfaces\ServiceManagerInterface */
        private $sm;

        public function setUp()
        {
            $this->di = new Forge;
            $this->sm = $this->di->getServices();
        }

        public function test00_construct()
        {
            $this->assertTrue((new Forge)->getInstance() === (new Forge)->getInstance());
            $this->sm->registerServiceProviders();
            
        }

        public function test01_instance()
        {
            $this->di->add(['standard', stdClass::class]);
            $this->assertTrue($this->di->get('standard') instanceof stdClass);
        }

        public function test02_add()
        {
            $this->assertTrue($this->di->add('function', function () { return 'I am a function.'; }) instanceof Forge);
            $this->assertEquals('I am a function.', $this->di['function']);

            $this->di->add('spanks', new TestServiceProvider($this->di));
            $this->assertTrue($this->di->get('spanks') instanceof \Og\Providers\TestServiceProvider);
        }

        public function test03_remove()
        {
            $di = $this->di;

            # following from test02...
            $this->di->remove('spanks');
            $this->assertFalse($di->has('spanks'));
        }

        public function test_DI()
        {
            $di = $this->di;

            $this->assertTrue($di->service('getInstance') instanceof IlluminateContainer);
            $this->assertTrue(di('ioc') instanceof IlluminateContainer);
            $this->assertTrue(di('di') instanceof Forge);
            $this->assertTrue(di('Og\Forge') instanceof Forge);
            //$this->assertTrue(di(League\Container\ContainerInterface::class) instanceof Forge);

            # tests singleton with closure
            $di->singleton('speck', function () { return 'speck'; });
            $this->assertEquals('speck', $di['speck']);

            $di->shared('elapsed_time', function () { return elapsed_time(); });
            $this->assertStringEndsWith('ms', $di->offsetGet('elapsed_time'));

            # note that the first argument to the function is always the DI
            # although we are not using it in this example
            $di->add('add_values', function ($di, array $params)
            {
                if ($di instanceof IlluminateContainer)
                    return $params[0] + $params[1];
                else return NULL;
            });
            $this->assertTrue($di->offsetExists('add_values'));
            $this->assertEquals(15, $di->get('add_values', [5, 10]));

        }

        public function test_Instances()
        {
            $di = $this->di;

            # verify di() equivalency
            $this->assertEquals($di, di());
            $this->assertEquals(Forge::getInstance(), di());

            $this->assertTrue($di->service('getInstance') instanceof Illuminate\Container\Container);
            $this->assertTrue(array_key_exists('Og\Forge', $di->service('getBindings')));
        }

        /**
         * @expectedException \InvalidArgumentException
         */
        public function test_InvalidArgumentException()
        {
            $this->di->get('google');
        }

        /**
         * @expectedException \BadMethodCallException
         */
        public function test_callException()
        {
            # this will fail with the expected exception
            $this->di->{'spooky'}();
        }

    }
