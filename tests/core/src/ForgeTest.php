<?php

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Illuminate\Container\Container as IlluminateContainer;
use Og\Forge;
use Og\Providers\TestServiceProvider;
use Og\Services;

/**
 * Test the framework core classes
 *
 * @group                  core
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ForgeTest extends PHPUnit_Framework_TestCase
{
    /** @var Forge */
    private $forge;

    /** @var \Og\Interfaces\ServiceManagerInterface */
    private $services;

    public function setUp()
    {
        $this->forge = new Forge;
        $this->services = new Services($this->forge);
    }

    public function test00_construct()
    {
        $this->assertTrue((new Forge)->getInstance() === (new Forge)->getInstance());
        $this->services->registerServiceProviders();
    }

    public function test01_instance()
    {
        $this->forge->add(['standard', stdClass::class]);
        $this->assertTrue($this->forge->get('standard') instanceof stdClass);
    }

    public function test02_add()
    {
        $this->assertTrue($this->forge->add('function', function () { return 'I am a function.'; }) === NULL);
        $this->assertEquals('I am a function.', $this->forge['function']);

        $this->forge->add('spanks', new TestServiceProvider($this->forge));
        $this->assertTrue($this->forge->get('spanks') instanceof \Og\Providers\TestServiceProvider);
    }

    public function test03_remove()
    {
        $di = $this->forge;

        # following from test02...
        $this->forge->remove('spanks');
        $this->assertFalse($di->has('spanks'));
    }

    public function test_DI()
    {
        $di = $this->forge;

        $this->assertTrue($di->container() instanceof IlluminateContainer);
        $this->assertTrue(forge('ioc') instanceof IlluminateContainer);
        $this->assertTrue(forge('forge') instanceof Forge);
        $this->assertTrue(forge('Og\Forge') instanceof Forge);

        # tests singleton with closure
        $di->singleton('speck', function () { return 'speck'; });
        $this->assertEquals('speck', $di['speck']);

        $di->shared('elapsed_time_since_request', function () { return elapsed_time_since_request(); });
        $this->assertStringEndsWith('ms', $di->offsetGet('elapsed_time_since_request'));

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
        $di = $this->forge;

        # verify forge() equivalency
        $this->assertEquals($di, forge());
        $this->assertEquals(Forge::getInstance(), forge());

        $this->assertTrue($di->container() instanceof Illuminate\Container\Container);
        $this->assertTrue(array_key_exists('Og\Forge', $di->container('getBindings')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_InvalidArgumentException()
    {
        $this->forge->get('google');
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function test_callException()
    {
        # this will fail with the expected exception
        $this->forge->{'spooky'}();
    }

}
