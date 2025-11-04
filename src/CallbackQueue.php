<?php
namespace Gamegos\Events;

/* Imports from PHP core */
use IteratorAggregate;
use Countable;
use ArrayIterator;

/**
 * Priority Queue Implementation for Callbacks
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class CallbackQueue implements IteratorAggregate, Countable
{
    /**
     * Callback storage
     */
    protected array $storage = [];

    /**
     * Add a callback into the queue.
     */
    public function add(callable $callback, int $priority = 0): void
    {
        $this->storage[$priority][] = $callback;
    }

    /**
     * Check if the queue contains a specified callback.
     */
    public function contains(callable $callback): bool
    {
        foreach (array_keys($this->storage) as $priority) {
            if (false !== array_search($callback, $this->storage[$priority], true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a callback from the queue.
     * If $callback is found more than once, all the refences will be removed.
     */
    public function remove(callable $callback): void
    {
        foreach (array_keys($this->storage) as $priority) {
            $indexes = array_keys($this->storage[$priority], $callback, true);
            foreach ($indexes as $index) {
                unset($this->storage[$priority][$index]);
                if (empty($this->storage[$priority])) {
                    unset($this->storage[$priority]);
                }
            }
        }
    }

    /**
     * Export the queue as a sorted array.
     */
    public function export(): array
    {
        $priorities = array_keys($this->storage);
        rsort($priorities, SORT_NUMERIC);

        $callbacks = [];
        foreach ($priorities as $priority) {
            $callbacks = array_merge($callbacks, $this->storage[$priority]);
        }
        return $callbacks;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->export());
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->storage as $callbacks) {
            $count += count($callbacks);
        }
        return $count;
    }
}
