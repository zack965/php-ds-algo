<?php


namespace Zack\PhpDsAlgo\Contracts;

use Countable;

interface IQueue extends Countable
{
    /**
     * Add an element to the rear of the queue.
     */
    public function enqueue(mixed $item): static;

    /**
     * Remove and return the front element.
     *
     * @throws InvalidArgumentException
     */
    public function dequeue(): mixed;


    /**
     * Return the front element without removing it.
     *
     * @throws InvalidArgumentException
     */
    public function front(): mixed;

    /**
     * Return the last element without removing it.
     *
     * @throws InvalidArgumentException
     */
    public function rear(): mixed;


    /**
     * Determine whether the queue contains no elements.
     */
    public function isEmpty(): bool;

    /**
     * Remove all elements from the queue.
     */
    public function clear(): static;

    /**
     * Return all queue elements from front to rear.
     *
     * @return array<int, mixed>
     */
    public function toArray(): array;
    /**
     * Return all queue elements from front to rear as an iterable.
     *
     * @return iterable<mixed>
     */
    public function toIterable(): iterable;


    /**
     * isFull
     *
     * @return bool
     */
    public function isFull(): bool;



    /**
     * contains
     *
     * @param  mixed $item
     * @return bool
     */
    public function contains(mixed $item): bool;
}
