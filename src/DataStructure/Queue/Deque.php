<?php


namespace Zack\PhpDsAlgo\DataStructure\Queue;

use Zack\PhpDsAlgo\Contracts\IDeque;

/**
 * A double-ended queue: {@see Queue} plus push/pop at the front, so items
 * can be added or removed from either end.
 *
 * Inherits {@see Queue::enqueue()} (rear) and {@see Queue::dequeue()}
 * (front) as-is, and adds the mirror pair for the other two operations.
 */
class Deque extends Queue implements IDeque
{
    /**
     * Add an element to the front of the deque.
     *
     * Unlike {@see Queue::enqueue()}, this does not check `isFull()` against
     * `maxCapacity` — it can push past the configured capacity.
     */
    public function enqueueFront(mixed $item): static
    {
        array_unshift($this->items, $item);
        return $this;
    }

    /**
     * Remove and return the rear element.
     *
     * Unlike {@see Queue::dequeue()}, this does not throw on an empty deque
     * — it returns `null` instead (the underlying `array_pop()`'s own
     * behavior on an empty array).
     */
    public function dequeueTail(): mixed
    {
        return array_pop($this->items);
    }
}
