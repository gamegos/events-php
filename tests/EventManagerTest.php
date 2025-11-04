<?php
namespace Gamegos\Events\Tests;

use InvalidArgumentException;
use ReflectionMethod;

/* Imports from Gamegos\Events */
use Gamegos\Events\EventManager;
use Gamegos\Events\Event;
use Gamegos\Events\EventInterface;

/* Imports from PHPUnit */
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Test class for Gamegos\Events\EventManager
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class EventManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Get event handlers from an event manager.
     * @param  \Gamegos\Events\EventManager $eventManager
     * @param  string $eventName
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
    public static function invalidEventNamesProvider(): array
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

    #[DataProvider('invalidEventNamesProvider')]
    public function testAttachThrowsExceptionForInvalidEventName($eventName)
    {
        $this->expectException(InvalidArgumentException::class);

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
        $defaultEvent = $this->createMock(EventInterface::class);
        $eventManager->setDefaultEvent($defaultEvent);

        // Test that the default event was set by creating a new event and checking it's cloned
        $reflection = new \ReflectionProperty($eventManager, 'defaultEvent');
        $reflection->setAccessible(true);
        $this->assertSame($defaultEvent, $reflection->getValue($eventManager));

        return [
            'eventManager' => $eventManager,
            'defaultEvent' => $defaultEvent,
        ];
    }

    #[Depends('testSetDefaultEvent')]
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

    #[Depends('testAttachHandlersToSingleEvent')]
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

    #[Depends('testAttachHandlerToMultipleEvents')]
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

    public function testTrigger()
    {
        $eventManager = new EventManager();
        $eventName    = 'foo';

        $callCount = 0;
        $testCase = $this;
        for ($i = 0; $i < 3; $i++) {
            $eventManager->attach($eventName, function($event) use (&$callCount, $testCase) {
                $callCount++;
                $testCase->assertInstanceOf(EventInterface::class, $event);
            });
        }
        $eventManager->trigger($eventName);
        $this->assertEquals(3, $callCount);
    }

    public function testTriggerEvent()
    {
        $eventManager = new EventManager();
        $eventName    = 'foo';
        $event        = new Event($eventName);

        $callCount = 0;
        $testCase = $this;
        for ($i = 0; $i < 3; $i++) {
            $eventManager->attach($eventName, function($receivedEvent) use (&$callCount, $testCase, $event) {
                $callCount++;
                $testCase->assertSame($event, $receivedEvent);
            });
        }
        $eventManager->triggerEvent($event);
        $this->assertEquals(3, $callCount);
    }

    /**
     * Data provider to test handlers with priority.
     * @return array
     */
    public static function triggerWithPriorityProvider(): array
    {
        $methods = [
            'methodA' => 2,
            'methodB' => 3,
            'methodC' => 1,
            'methodD' => 2,
            'methodE' => 1,
            'methodF' => 3,
        ];
        // Sort priorities in reverse order, do not modify relative positions with same priority.
        uasort($methods, function ($a, $b) {
            return $a <= $b ? 1 : -1;
        });

        return [
            [new EventManager(), 'foo', $methods]
        ];
    }

    #[DataProvider('triggerWithPriorityProvider')]
    public function testTriggerWithPriority(EventManager $eventManager, string $eventName, array $methods)
    {
        $executionOrder = [];
        foreach ($methods as $method => $priority) {
            $eventManager->attach($eventName, function($event) use ($method, &$executionOrder) {
                $executionOrder[] = $method;
            }, $priority);
        }
        $eventManager->trigger($eventName);
        
        // Verify methods were called in correct priority order
        $expectedOrder = array_keys($methods);
        $this->assertEquals($expectedOrder, $executionOrder);
    }

    #[DataProvider('triggerWithPriorityProvider')]
    public function testTriggerEventWithPriority(EventManager $eventManager, string $eventName, array $methods)
    {
        $event = new Event($eventName);
        $executionOrder = [];
        $testCase = $this;
        
        foreach ($methods as $method => $priority) {
            $eventManager->attach($eventName, function($receivedEvent) use ($method, &$executionOrder, $testCase, $event) {
                $executionOrder[] = $method;
                $testCase->assertSame($event, $receivedEvent);
            }, $priority);
        }
        $eventManager->triggerEvent($event);
        
        // Verify methods were called in correct priority order  
        $expectedOrder = array_keys($methods);
        $this->assertEquals($expectedOrder, $executionOrder);
    }

    public function testStopPropagation()
    {
        $eventName = 'foo';
        $executionOrder = [];
        $testCase = $this;

        $eventManager = new EventManager();
        
        $eventManager->attach($eventName, function(EventInterface $event) use (&$executionOrder, $testCase) {
            $executionOrder[] = 'beforeStop';
            $testCase->assertInstanceOf(EventInterface::class, $event);
        });
        
        $eventManager->attach($eventName, function(EventInterface $event) use (&$executionOrder, $testCase) {
            $executionOrder[] = 'stop';
            $testCase->assertInstanceOf(EventInterface::class, $event);
            $event->stopPropagation();
        });
        
        $eventManager->attach($eventName, function(EventInterface $event) use (&$executionOrder) {
            $executionOrder[] = 'afterStop';
        });
        
        $eventManager->trigger($eventName);
        
        // Verify only beforeStop and stop were called, not afterStop
        $this->assertEquals(['beforeStop', 'stop'], $executionOrder);
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
