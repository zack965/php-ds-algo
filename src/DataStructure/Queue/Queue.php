<?php


namespace Zack\PhpDsAlgo\DataStructure\Queue;

use InvalidArgumentException;
use Zack\PhpDsAlgo\Contracts\IQueue;

class Queue implements IQueue
{
    public function __construct(private array $items = []) {}

    private int $maxCapacity;


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
        if (count($this->items) > $this->maxCapacity) {
            throw new InvalidArgumentException("The capacity id full");
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
     */
    public function setMaxCapacity(int $maxCapacity): self
    {
        $this->maxCapacity = $maxCapacity;

        return $this;
    }


    public function front(): mixed
    {
        return $this->items[0];
    }

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
        return count($this->items) == $this->maxCapacity;
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
