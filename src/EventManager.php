<?php
namespace Gamegos\Events;

/* Imports from PHP core */
use InvalidArgumentException;

/**
 * Event Manager
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class EventManager
{
    /**
     * Events and their associated handlers.
     * @var array<string, CallbackQueue>
     */
    protected array $events = [];

    /**
     * Default event object.
     */
    protected ?EventInterface $defaultEvent = null;

    /**
     * Set the default event object.
     */
    public function setDefaultEvent(EventInterface $event): void
    {
        $this->defaultEvent = $event;
    }

    /**
     * Attach an handler to an event or multiple events.
     * @param string|array<string> $eventName Event name or array of event names
     * @throws InvalidArgumentException
     */
    public function attach(mixed $eventName, callable $handler, int $priority = 0): void
    {
        if (is_array($eventName)) {
            foreach ($eventName as $name) {
                $this->attach($name, $handler, $priority);
            }
            return;
        }
        
        if (!is_string($eventName)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects argument 1 to be string; %s given.',
                __METHOD__,
                is_object($eventName) ? get_class($eventName) : gettype($eventName)
            ));
        }

        if (!array_key_exists($eventName, $this->events)) {
            $this->events[$eventName] = new CallbackQueue();
        }

        $this->events[$eventName]->add($handler, $priority);
    }

    /**
     * Detach an handler from an event.
     */
    public function detach(string $eventName, callable $handler): void
    {
        if (isset($this->events[$eventName])) {
            $this->events[$eventName]->remove($handler);
            if (0 == count($this->events[$eventName])) {
                unset($this->events[$eventName]);
            }
        }
    }

    /**
     * Create an event from the default event and trigger it's handlers.
     */
    public function trigger(string $eventName, mixed $target = null): void
    {
        $event = $this->createEvent($eventName);
        $event->setTarget($target);
        $this->triggerHandlers($event);
    }

    /**
     * Trigger handlers of an event.
     */
    public function triggerEvent(EventInterface $event): void
    {
        $this->triggerHandlers($event);
    }

    /**
     * Get handlers of an event.
     */
    protected function getHandlers(string $eventName): CallbackQueue
    {
        return $this->events[$eventName] ?? new CallbackQueue();
    }

    /**
     * Create an event.
     */
    protected function createEvent(string $eventName): EventInterface
    {
        if (null === $this->defaultEvent) {
            return new Event($eventName);
        }
        $event = clone $this->defaultEvent;
        $event->setName($eventName);
        return $event;
    }

    /**
     * Trigger handlers of an event.
     */
    protected function triggerHandlers(EventInterface $event): void
    {
        foreach ($this->getHandlers($event->getName()) as $handler) {
            call_user_func($handler, $event);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
    }
}
