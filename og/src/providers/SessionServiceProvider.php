<?php namespace Og\Providers;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Aura\Session\Session;
use Aura\Session\SessionFactory;
use Og\Forge;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Forge $di */
        $di = $this->container;

        $session_factory = new SessionFactory;
        $session = $session_factory->newInstance($_COOKIE);

        $session->setName('cogs');
        $session->start();

        # fail if the session start fails
        if ( ! $session->isStarted())
            throw new \LogicException('Cannot continue: unable to start a new session.');

        # remember the session
        $di->singleton(['session', Session::class], $session);

        $this->provides += [Session::class];
    }
}
