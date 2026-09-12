<?php


namespace Zack\PhpDsAlgo\Contracts;

/**
 * Double-ended queue contract: a queue that can also be pushed/popped from
 * the front, in addition to the standard rear-enqueue/front-dequeue pair
 * inherited from {@see IQueue}.
 */
interface IDeque extends IQueue
{
    /**
     * Add an element to the front of the deque.
     */
    public function enqueueFront(mixed $item): static;

    /**
     * Remove and return the rear element.
     */
    public function dequeueTail(): mixed;
}
