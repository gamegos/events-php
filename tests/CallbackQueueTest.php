<?php
namespace Gamegos\Events\Tests;

/* Import from PHP Core */
use ArrayIterator;

/* Imports from Gamegos\Events */
use Gamegos\Events\CallbackQueue;

/* Imports from PHPUnit */
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for Gamegos\Events\CallbackQueue
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class CallbackQueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Number of dummy callbacks.
     * @var int
     */
    const NUM_DUMMY_CALLBACKS = 15;

    /**
     * Number of dummy priorities.
     * @var int
     */
    const NUM_DUMMY_PRIORITIES = 3;

    /**
     * Dummy callbacks
     * @var array
     */
    protected $dummyCallbacks = [];

    /**
     * Shuffled dummy callbacks.
     * @var array
     */
    protected $shuffledDummyCallbacks = [];

    /**
     * Get shuffling keys for dummy callbacks.
     * We use this to get a fixed result after shuffled the callbacks.
     * @return int[]
     */
    protected static function getShufflingKeys()
    {
        return [5, 9, 6, 15, 10, 7, 12, 13, 11, 1, 3, 8, 14, 2, 4];
    }

    /**
     * Get dummy callbacks.
     * @param  boolean $shuffled
     * @return array
     */
    protected function getDummyCallbacks($shuffled = false)
    {
        if (empty($this->dummyCallbacks)) {
            $priority = self::NUM_DUMMY_PRIORITIES;
            for ($i = 0; $i < self::NUM_DUMMY_CALLBACKS; $i++) {
                $order = $i % ceil(self::NUM_DUMMY_CALLBACKS / self::NUM_DUMMY_PRIORITIES);
                if (0 == $order) {
                    $priority--;
                }
                $this->dummyCallbacks[] = [
                    'callback' => function() use ($priority, $order) { return "{$priority}.{$order}"; },
                    'priority' => $priority,
                    'addIndex' => $order,
                ];
            }
        }
        if ($shuffled) {
            if (empty($this->shuffledDummyCallbacks)) {
                $this->shuffledDummyCallbacks = $this->dummyCallbacks;
                // Shuffle items by shuffling keys.
                $shufflingKeys = self::getShufflingKeys();
                array_multisort($shufflingKeys, $this->shuffledDummyCallbacks);
                // Correct adding order of items.
                usort($this->shuffledDummyCallbacks, function ($a, $b) {
                    if ($a['addIndex'] == $b['addIndex']) {
                        return 0;
                    }
                    return ($a['addIndex'] < $b['addIndex']) ? -1 : 1;
                });
            }
            return $this->shuffledDummyCallbacks;
        }
        return $this->dummyCallbacks;
    }

    /**
     * Get info of a dummy callback by shuffle key.
     * Each shuffle key identifies a specific callback.
     * @param  int $shuffleKey
     * @return array
     */
    protected function getDummyCallback($shuffleKey)
    {
        return $this->getDummyCallbacks(true)[$shuffleKey];
    }

    /**
     * Get a callback queue filled with dummy callbacks.
     * @param  boolean $shuffled
     * @return \Gamegos\Events\CallbackQueue
     * @codeCoverageIgnore
     */
    protected function getDummyFilledQueue($shuffled = false)
    {
        $queue = new CallbackQueue();
        foreach ($this->getDummyCallbacks($shuffled) as $item) {
            $queue->add($item['callback'], $item['priority']);
        }
        return $queue;
    }

    /**
     * Get dummy callbacks with expected call order.
     * @return callable[]
     */
    protected function getExpectedDummyCallbacks()
    {
        $callbacks = [];
        foreach ($this->getDummyCallbacks() as $item) {
            $callbacks[] = $item['callback'];
        }
        return $callbacks;
    }

    /**
     * Data provider for userPriority flag.
     * @return array
     */
    public static function usePriorityProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    #[DataProvider('usePriorityProvider')]
    public function testAddContainsAndRemove($usePriority)
    {
        $queue     = new CallbackQueue();
        $testItems = [
            $this->getDummyCallback(2),
            $this->getDummyCallback(5),
            $this->getDummyCallback(13),
        ];

        for ($i = 0; $i < 3; $i++) {
            $item = $testItems[$i];
            // Add callback into queue.
            if ($usePriority) {
                $queue->add($item['callback'], $item['priority']);
            } else {
                $queue->add($item['callback']);
            }
            // Validate new callback in queue.
            $this->assertTrue($queue->contains($item['callback']));
            // Validate number of callbacks in queue.
            $this->assertCount($i + 1, $queue);
        }
        for ($i = 2; $i >= 0; $i--) {
            $item = $testItems[$i];
            // Remove callback from queue.
            $queue->remove($item['callback']);
            // Validate removed callback from queue.
            $this->assertFalse($queue->contains($item['callback']));
            // Validate number of callbacks in queue.
            $this->assertCount($i, $queue);
        }
    }

    public function testExportReturnsSortedCallbacks()
    {
        $queue    = $this->getDummyFilledQueue(true);
        $expected = $this->getExpectedDummyCallbacks();

        $this->assertSame($expected, $queue->export());
    }

    public function testGetIteratorReturnsIteratorOfSortedCallbacks()
    {
        $actual   = $this->getDummyFilledQueue(true)->getIterator();
        $expected = new ArrayIterator($this->getExpectedDummyCallbacks());

        $this->assertEquals($expected, $actual);
    }
}
