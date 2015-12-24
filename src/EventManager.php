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
     * @var \Gamegos\Events\CallbackQueue[]
     */
    protected $events = [];

    /**
     * Default event object.
     * @var \Gamegos\Events\Event
     */
    protected $defaultEvent;

    /**
     * Set the default event object.
     * @param \Gamegos\Events\EventInterface $event
     */
    public function setDefaultEvent(EventInterface $event)
    {
        $this->defaultEvent = $event;
    }

    /**
     * Attach an handler to an event or multiple events.
     * @param  string|array $eventName
     *         Event name or array of event names
     * @param  callable $handler
     * @param  int $priority
     * @throws \InvalidArgumentException
     */
    public function attach($eventName, callable $handler, $priority = 0)
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
     * @param string $eventName
     * @param callable $handler
     */
    public function detach($eventName, callable $handler)
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
     * @param string $eventName
     * @param mixed $target
     */
    public function trigger($eventName, $target = null)
    {
        $event = $this->createEvent($eventName);
        $event->setTarget($target);
        $this->triggerHandlers($event);
    }

    /**
     * Trigger handlers of an event.
     * @param \Gamegos\Events\EventInterface $event
     */
    public function triggerEvent(EventInterface $event)
    {
        $this->triggerHandlers($event);
    }

    /**
     * Get handlers of an event.
     * @param  string $eventName
     * @return \Gamegos\Events\CallbackQueue
     */
    protected function getHandlers($eventName)
    {
        return isset($this->events[$eventName]) ? $this->events[$eventName] : new CallbackQueue();
    }

    /**
     * Create an event.
     * @param  string $eventName
     * @return \Gamegos\Events\EventInterface
     */
    protected function createEvent($eventName)
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
     * @param \Gamegos\Events\EventInterface $event
     */
    protected function triggerHandlers(EventInterface $event)
    {
        foreach ($this->getHandlers($event->getName()) as $handler) {
            call_user_func($handler, $event);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
    }
}
