<?php
namespace Gamegos\Events\Tests;

use InvalidArgumentException;
use ReflectionMethod;

/* Imports from Gamegos\Events */
use Gamegos\Events\EventManager;
use Gamegos\Events\Event;
use Gamegos\Events\EventInterface;

/**
 * Test class for Gamegos\Events\EventManager
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class EventManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get event handlers from an event manager.
     * @param  \Gamegos\Events\EventManager $eventManager a
     * @param  string $eventName a
     * @return \Gamegos\Events\CallbackQueue
     */
    protected function getEventHandlers(EventManager $eventManager, $eventName)
    {
        $rm = new ReflectionMethod(EventManager::class, 'getHandlers');
        return call_user_func($rm->getClosure($eventManager), $eventName);
    }

    /**
     * Create an event instance from an event manager.
     * @param \Gamegos\Events\EventManager $eventManager
     * @param string $eventName
     */
    protected function createEvent(EventManager $eventManager, $eventName)
    {
        $rm = new ReflectionMethod(EventManager::class, 'createEvent');
        return call_user_func($rm->getClosure($eventManager), $eventName);
    }

    /**
     * Invalid event names provider.
     * @return array
     */
    public function invalidEventNamesProvider()
    {
        $values = [
            'null'       => [null],
            'bool-true'  => [true],
            'bool-false' => [false],
            'int'        => [1],
            'int-zero'   => [0],
            'float'      => [1.1],
            'object'     => [(object) ['foo' => 'bar']],
            'resource'   => [fopen('php://memory', 'r')],
        ];

        foreach ($values as $id => $arguments) {
            $values['bad-array-' . $id] = [
                [$arguments[0], 'valid-event-name'],
            ];
        }

        return $values;
    }

    /**
     * @dataProvider invalidEventNamesProvider
     */
    public function testAttachThrowsExceptionForInvalidEventName($eventName)
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $eventManager = new EventManager();
        $eventManager->attach($eventName, function ($e) {
        });
    }

    public function testCreateEventReturnsPredefinedEvent()
    {
        $eventManager = new EventManager();
        $expected     = Event::class;
        $actual       = get_class($this->createEvent($eventManager, 'foo'));
        $this->assertEquals($expected, $actual);
    }

    public function testSetDefaultEvent()
    {
        $eventManager = new EventManager();
        $defaultEvent = $this->prophesize(EventInterface::class)->reveal();
        $eventManager->setDefaultEvent($defaultEvent);

        $this->assertAttributeSame($defaultEvent, 'defaultEvent', $eventManager);

        return [
            'eventManager' => $eventManager,
            'defaultEvent' => $defaultEvent,
        ];
    }

    /**
     * @depends testSetDefaultEvent
     */
    public function testCreateEventAfterSetDefaultEvent($params)
    {
        $eventManager = $params['eventManager'];
        $defaultEvent = $params['defaultEvent'];

        $expected = get_class($defaultEvent);
        $actual   = get_class($this->createEvent($eventManager, 'foo'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox getHandlers() returns empty CallbackQueue object if event does not exist
     */
    public function testGetHandlersReturnsEmpty()
    {
        $eventManager = new EventManager();
        $this->assertEmpty($this->getEventHandlers($eventManager, 'foo'));
    }

    public function testAttachHandlersToSingleEvent()
    {
        $eventHandlerMap = [
            'foo' => [
                function ($e) {
                },
                function ($e) {
                },
                function ($e) {
                },
            ],
            'bar' => [
                function ($e) {
                },
                function ($e) {
                },
                function ($e) {
                },
            ],
            'baz' => [
                function ($e) {
                },
                function ($e) {
                },
                function ($e) {
                },
            ],
        ];

        $eventManager = new EventManager();
        foreach ($eventHandlerMap as $eventName => $handlers) {
            foreach ($handlers as $handler) {
                $countBefore = count($this->getEventHandlers($eventManager, $eventName));
                $eventManager->attach($eventName, $handler);
                $this->assertAttachedHandler($handler, $this->getEventHandlers($eventManager, $eventName), $countBefore + 1);
            }
        }

        return [
            'eventManager'    => $eventManager,
            'eventHandlerMap' => $eventHandlerMap,
        ];
    }

    /**
     * @depends testAttachHandlersToSingleEvent
     */
    public function testDetachHandlersFromSingleEvent($params)
    {
        $eventManager    = $params['eventManager'];
        $eventHandlerMap = $params['eventHandlerMap'];

        foreach ($eventHandlerMap as $eventName => $handlers) {
            foreach ($handlers as $handler) {
                $countBefore = count($this->getEventHandlers($eventManager, $eventName));
                $eventManager->detach($eventName, $handler);
                $this->assertDetachedHandler($handler, $this->getEventHandlers($eventManager, $eventName), $countBefore - 1);
            }
        }
    }

    public function testAttachHandlerToMultipleEvents()
    {
        $eventNames = ['foo', 'bar', 'baz'];
        $handler    = function ($e) {
        };

        $eventManager = new EventManager();
        $eventManager->attach($eventNames, $handler);

        foreach ($eventNames as $eventName) {
            $this->assertAttachedHandler($handler, $this->getEventHandlers($eventManager, $eventName), 1);
        }

        return [
            'eventManager' => $eventManager,
            'eventNames'   => $eventNames,
            'handler'      => $handler,
        ];
    }

    /**
     * @depends testAttachHandlerToMultipleEvents
     */
    public function testDetachHandlerFromMultipleEvents($params)
    {
        /* @var $eventManager EventManager */
        $eventManager = $params['eventManager'];
        $eventNames   = $params['eventNames'];
        $handler      = $params['handler'];

        foreach ($eventNames as $eventName) {
            $countBefore = count($this->getEventHandlers($eventManager, $eventName));
            $eventManager->detach($eventName, $handler);
            $this->assertDetachedHandler($handler, $this->getEventHandlers($eventManager, $eventName), $countBefore - 1);
        }
    }

    public function testTriggerAttachedHandlers()
    {
        $eventName    = 'foo';
        $eventManager = new EventManager();

        for ($i = 0; $i < 3; $i++) {
            $listener = $this->getMock('stdClass', ['method']);
            $listener->expects($this->once())->method('method')->with($this->isInstanceOf(EventInterface::class));
            $eventManager->attach($eventName, [$listener, 'method']);
        }
        $eventManager->trigger($eventName);
    }

    public function testTriggerAttachedHandlersWithPriority()
    {
        $eventName  = 'foo';
        $priorities = [
            'methodA' => 2,
            'methodB' => 3,
            'methodC' => 1,
            'methodD' => 2,
            'methodE' => 1,
            'methodF' => 3,
        ];
        // Sort priorities in reverse order, do not modify relative positions with same priority.
        uasort($priorities, function ($a, $b) {
            return $a <= $b ? 1 : -1;
        });

        $listener     = $this->getMock('stdClass', array_keys($priorities));
        $eventManager = new EventManager();

        $index = 0;
        foreach ($priorities as $method => $priority) {
            $listener->expects($this->at($index++))->method($method)->with($this->isInstanceOf(EventInterface::class));
            $eventManager->attach($eventName, [$listener, $method], $priority);
        }
        $eventManager->trigger($eventName);
    }

    public function testStopPropagation()
    {
        $eventName = 'foo';

        $instanceOf = $this->isInstanceOf(EventInterface::class);
        $stopMethod = function (EventInterface $e) {
            $e->stopPropagation();
        };

        $listener = $this->getMock('stdClass', ['beforeStop', 'stop', 'afterStop']);
        $listener->expects($this->at(0))->method('beforeStop')->with($instanceOf);
        $listener->expects($this->at(1))->method('stop')->with($instanceOf)->willReturnCallback($stopMethod);
        $listener->expects($this->never())->method('afterStop')->with($instanceOf);

        $eventManager = new EventManager();
        $eventManager->attach($eventName, [$listener, 'beforeStop']);
        $eventManager->attach($eventName, [$listener, 'stop']);
        $eventManager->attach($eventName, [$listener, 'afterStop']);
        $eventManager->trigger($eventName);
    }

    /**
     * Assert attached handler.
     * @param callable $handler
     * @param \Gamegos\Events\CallbackQueue $handlers
     * @param int $expectedCount
     */
    protected function assertAttachedHandler(callable $handler, $handlers, $expectedCount)
    {
        $this->assertContains($handler, $handlers);
        $this->assertCount($expectedCount, $handlers);
    }

    /**
     * Seert detached handler.
     * @param callable $handler
     * @param \Gamegos\Events\CallbackQueue $handlers
     * @param int $expectedCount
     */
    protected function assertDetachedHandler(callable $handler, $handlers, $expectedCount)
    {
        $this->assertNotContains($handler, $handlers);
        $this->assertCount($expectedCount, $handlers);
    }
}
