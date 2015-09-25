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
    class ContextTest extends \PHPUnit_Framework_TestCase
    {
        /** @var Context */
        private $context;

        /**
         * @expectedException \LogicException
         */
        public function _test_Exception_SingleSession()
        {
            # this should fail with a LogicException because the Context has already
            # been instantiated.
            new Context(Forge::getInstance(), new Events(Forge::getInstance()));
        }

        public function setUp()
        {
            $di = Forge::getInstance();
            $this->context = $di['context'];
        }

        public function test_ContextCollection()
        {
            $context = $this->context;
            $context['contents'] = "A Context String";
            $this->assertEquals('A Context String', $context['contents']);
            $context->contents = "A different song.";
            $this->assertEquals('A different song.', $context['contents']);
        }

        public function test_ContextEvents()
        {
            $context = $this->context;

            di('events')->on('context.test.event', function (Context $context)
            {
                $context->thaw();
                $context['test.event'] = elapsed_time(TRUE);
            });

            di('events')->notify('context.test.event', $context);

        }

        public function test_ContextMacros()
        {

        }

        public function test_Exception_Mutability()
        {
            $context = $this->context;
            $context->thaw();
            $context->applies = 'application of applies';

            # make the context immutable
            $context->freeze();

            # this is now forbidden
            $this->setExpectedException(
                'Og\\Exceptions\\CollectionMutabilityError',
                'Mutability Violation: the key "applies" is set to read_only.'
            );
            $context->applies = 'try again';
        }

    }
