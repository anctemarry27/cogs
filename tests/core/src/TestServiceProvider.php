<?php namespace Og\Providers;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class TestServiceProvider extends ServiceProvider
{

    /**
     * Replaces test_provider callable with a new callable.
     */
    function boot()
    {
        $this->forge->remove('test_provider');
        $this->forge->add('test_provider', function () { return 'I am a modified test provider.'; });
    }

    function register()
    {
        $this->forge->add('test_provider', function () { return 'I am a test provider.'; });
    }
}
