<?php
namespace Gamegos\Events\Tests;

/* Imports from Gamegos\Events */
use Gamegos\Events\Event;

/**
 * Test class for Gamegos\Events\Event
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class EventTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorArguments()
    {
        $name   = 'foo';
        $target = 'bar';

        $event = new Event($name, $target);

        $this->assertSame($name, $event->getName());
        $this->assertSame($target, $event->getTarget());
    }

    public function testPropagationIsNotStoppedByDefault()
    {
        $event = new Event('foo', 'bar');
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testStopPropagation()
    {
        $event = new Event('foo', 'bar');
        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }
}
