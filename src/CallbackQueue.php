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
     * @var array
     */
    protected $storage = [];

    /**
     * Add a callback into the queue.
     * @param callable $callback
     * @param int $priority
     */
    public function add(callable $callback, $priority = 0)
    {
        $this->storage[(int) $priority][] = $callback;
    }

    /**
     * Check if the queue contains a specified callback.
     * @param  callable $callback
     * @return boolean
     */
    public function contains(callable $callback)
    {
        foreach (array_keys($this->storage) as $priority) {
            if (false !== ($index = array_search($callback, $this->storage[$priority], true))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a callback from the queue.
     * If $callback is found more than once, all the refences will be removed.
     * @param callable $callback
     */
    public function remove(callable $callback)
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
     * @return callable[]
     */
    public function export()
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
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->export());
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $count = 0;
        foreach ($this->storage as $callbacks) {
            $count += count($callbacks);
        }
        return $count;
    }
}
