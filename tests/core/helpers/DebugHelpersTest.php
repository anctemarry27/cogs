<?php
    /**
     * @package Radium Codex
     * @author  Greg Truesdell <odd.greg@gmail.com>
     */
    
    /**
     * Test the framework Debug support functions
     *
     * @backupGlobals          disabled
     * @backupStaticAttributes disabled
     */
    class DebugHelpersTest extends \PHPUnit_Framework_TestCase
    {
        public function setUp()
        {
            define('PHPUNIT_TESTS', 1);
        }

        public function test01()
        {
            ### elapsed_time()

            $now = elapsed_time(TRUE);
            $this->assertGreaterThan($now, elapsed_time(TRUE));
            $this->assertStringEndsWith('ms', elapsed_time());
            $this->assertStringEndsNotWith('ms', (string) elapsed_time(TRUE));

            ### location_from_backtrace($index = 2)

            # The current location should be the call to ReflectionMethod.
            # Be aware that this may change if PHPUnit alters its execution pipeline.
            $this->assertStringEndsWith('ReflectionMethod::invokeArgs', location_from_backtrace());

            ### expose($value = NULL, $depth = 8)

            # these functions would otherwise halt the tests, so output is restricted
            # by use of the PHPUNIT_TESTS constant. The best we can do (until I find
            # another solution) is to call them for code coverage.
            
            //expose("expose test");
            //ddump('ddump test');

            ### readable_error_type($error_type)

            #@formatter:off
            $this->assertEquals('E_COMPILE_ERROR',      readable_error_type(E_COMPILE_ERROR));
            $this->assertEquals('E_COMPILE_WARNING',    readable_error_type(E_COMPILE_WARNING));
            $this->assertEquals('E_CORE_ERROR',         readable_error_type(E_CORE_ERROR));
            $this->assertEquals('E_CORE_WARNING',       readable_error_type(E_CORE_WARNING));
            $this->assertEquals('E_DEPRECATED',         readable_error_type(E_DEPRECATED));
            $this->assertEquals('E_ERROR',              readable_error_type(E_ERROR));
            $this->assertEquals('E_NOTICE',             readable_error_type(E_NOTICE));
            $this->assertEquals('E_PARSE',              readable_error_type(E_PARSE));
            $this->assertEquals('E_RECOVERABLE_ERROR',  readable_error_type(E_RECOVERABLE_ERROR));
            $this->assertEquals('E_STRICT',             readable_error_type(E_STRICT));
            $this->assertEquals('E_USER_DEPRECATED',    readable_error_type(E_USER_DEPRECATED));
            $this->assertEquals('E_USER_ERROR',         readable_error_type(E_USER_ERROR));
            $this->assertEquals('E_USER_NOTICE',        readable_error_type(E_USER_NOTICE));
            $this->assertEquals('E_USER_WARNING',       readable_error_type(E_USER_WARNING));
            $this->assertEquals('E_WARNING',            readable_error_type(E_WARNING));
            $this->assertEquals('UNKNOWN_ERROR',        readable_error_type('NON_EXISTENT_ERROR'));
        }

    }
