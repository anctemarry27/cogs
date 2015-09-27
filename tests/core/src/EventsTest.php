<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Cogs\Collections\Collection;

/**
 * Test the framework core classes
 *
 * @group                  core
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class EventsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Events */
    private $events;

    public function setUp()
    {
        $this->events = Forge::make(Events::class);
    }

    public function test_CollectionEvent()
    {
        return 'collection.fired';
    }

    public function test_Events()
    {
        $events = $this->events;
        $this->assertEquals($events, Forge::make('events'));

        $events->flush('test.event');

        $events->on('test.event', function ($message, $when) use ($events)
        {
            $this->assertEquals('I made it.', $message);
            $this->assertTrue(is_float($when));
            $this->assertTrue($when < microtime(TRUE));
            $this->assertEquals('test.event', $events->firing());

            return ['status' => 'successful'];
        });

        $this->assertFalse($events->firing());
        $result = $events->notify('test.event', ['message' => 'I made it.', 'when' => microtime(TRUE)])[0];
        $this->assertEquals('successful', $result['status']);

        $events->fire('test.event', ['message' => 'I made it.', 'when' => microtime(TRUE)], TRUE);
        $this->assertTrue($events->hasListeners('test.event'));
        $events->forget('test.event_queued');
        $events->forget('test.event');
        $this->assertFalse($events->hasListeners('test.event'));
        $events->forgetQueued();
    }

    public function test_GlobalEvents()
    {
        $events = $this->events;

        $events->on('*', function ()
        {

        });
    }

    public function test_Listening()
    {
        $di = Forge::getInstance();
        /** @var Collection $collection */
        $collection = new Collection;

        /** @var Events $events */
        $events = $di['events'];
        $events->subscribe(Collection::class);

        # we start with nothing
        $this->assertFalse($collection->any());

        # now listen for a collection new item
        $events->listen('collection.new.item', EventsTest::class . '@test_CollectionEvent');
        $this->assertEquals('collection.fired', $events->fire('collection.new.item')[0]);

    }

}
