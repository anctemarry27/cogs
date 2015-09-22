<?php
    /**
     * @package Og
     * @version 0.1.0
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */

    namespace Og;

    /**
     * Test the framework core classes
     *
     * @group                  core
     * @backupGlobals          disabled
     * @backupStaticAttributes disabled
     */
    class ServiceManagerTest extends \PHPUnit_Framework_TestCase
    {
        /** @var Forge */
        public $di;

        /** @var Services */
        public $sm;

        public function setUp()
        {
            $this->di = Forge::getInstance();
            $this->sm = new Services($this->di);
        }

        public function test__add_register_boot_ServiceProviders()
        {
            $this->assertFalse($this->di->has('test_provider'),
                'test_provider must not exist.');

            $this->sm->add(TestServiceProvider::class, TRUE);
            $this->assertTrue($this->di->has(TestServiceProvider::class),
                'TestServiceProvider::class must exist in the DI.');

            $this->sm->registerServiceProviders();
            $this->assertTrue($this->di->has('test_provider'),
                'test_provider must exist.');

            $this->assertEquals($this->di['test_provider'], 'I am a test provider.',
                'test_provider must return a predetermined string.');

            $this->sm->bootAll();

            $this->assertEquals($this->di['test_provider'], 'I am a modified test provider.',
                'test_provider must return a modified string.');

        }
        
    }
