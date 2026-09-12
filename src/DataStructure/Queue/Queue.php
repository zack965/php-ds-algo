<?php


namespace Zack\PhpDsAlgo\DataStructure\Queue;

use InvalidArgumentException;
use Zack\PhpDsAlgo\Contracts\IQueue;

/**
 * A mutable, array-backed FIFO queue with an optional capacity cap.
 *
 * `maxCapacity` defaults to `PHP_INT_MAX` (effectively unbounded) unless
 * given to the constructor or set later via {@see setMaxCapacity()} —
 * `enqueue()` throws once the queue reaches it.
 */
class Queue implements IQueue
{
    /**
     * @param array<int, mixed> $items Initial items, front to rear.
     * @param int $maxCapacity Defaults to effectively unbounded.
     *
     * @throws InvalidArgumentException if $maxCapacity is smaller than the
     *                                   number of initial $items.
     */
    public function __construct(
        protected array $items = [],
        private int $maxCapacity = PHP_INT_MAX,
    ) {
        if ($this->maxCapacity < count($this->items)) {
            throw new InvalidArgumentException(
                'Maximum capacity cannot be smaller than the number of initial items.'
            );
        }
    }


    /**
     * count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
    /**
     * Add an element to the rear of the queue.
     */
    public function enqueue(mixed $item): static
    {
        if ($this->isFull()) {
            throw new InvalidArgumentException('The queue is full.');
        }

        $this->items[] = $item;
        return $this;
    }


    /**
     * Remove and return the front element.
     *
     * @throws InvalidArgumentException
     */
    public function dequeue(): mixed
    {
        if (count($this->items) == 0) {
            throw new InvalidArgumentException("The Queue is empty");
        }
        return array_shift($this->items);
    }
    /**
     * getMaxCapacity
     *
     * @return int
     */
    public function getMaxCapacity(): int
    {
        return $this->maxCapacity;
    }

    /**
     * setMaxCapacity
     *
     * @param  int $maxCapacity
     * @return self
     *
     * @throws InvalidArgumentException if $maxCapacity is smaller than the
     *                                   queue's current size.
     */
    public function setMaxCapacity(int $maxCapacity): self
    {
        if ($maxCapacity < $this->count()) {
            throw new InvalidArgumentException(
                'Maximum capacity cannot be smaller than the current queue size.'
            );
        }

        $this->maxCapacity = $maxCapacity;

        return $this;
    }


    /**
     * Return the front element without removing it.
     *
     * Unlike the `@throws` documented on {@see \Zack\PhpDsAlgo\Contracts\IQueue::front()},
     * this implementation does not throw on an empty queue — accessing
     * index `0` of an empty `$items` array emits a PHP warning and
     * evaluates to `null`.
     */
    public function front(): mixed
    {
        return $this->items[0];
    }

    /**
     * Return the last element without removing it.
     *
     * Unlike the `@throws` documented on {@see \Zack\PhpDsAlgo\Contracts\IQueue::rear()},
     * this implementation does not throw on an empty queue — accessing a
     * negative index (`count($this->items) - 1` is `-1` when empty) emits a
     * PHP warning and evaluates to `null`.
     */
    public function rear(): mixed
    {
        return $this->items[count($this->items) - 1];
    }


    /**
     * Determine whether the queue contains no elements.
     */
    public function isEmpty(): bool
    {
        return count($this->items) == 0;
    }

    /**
     * Remove all elements from the queue.
     */
    public function clear(): static
    {
        $this->items = [];
        return $this;
    }

    /**
     * Return all queue elements from front to rear.
     *
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Return all queue elements from front to rear as an iterable.
     *
     * @return iterable<mixed>
     */
    public function toIterable(): iterable
    {
        yield from $this->items;
    }
    /**
     * isFull
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return count($this->items) >= $this->maxCapacity;
    }

    /**
     * contains
     *
     * @param  mixed $item
     * @return bool
     */
    public function contains(mixed $item): bool
    {
        return in_array($item, $this->items, true);
    }
}
