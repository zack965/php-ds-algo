<?php

namespace Tests\Unit\DataStructure\Queue;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Queue\Deque;

class DequeTest extends TestCase
{
    // --- enqueueFront ---

    public function testEnqueueFrontAddsItemToFrontOfEmptyDeque(): void
    {
        $deque = new Deque([]);

        $result = $deque->enqueueFront(1);

        $this->assertSame([1], $result->toArray());
    }

    public function testEnqueueFrontAddsItemBeforeExistingItems(): void
    {
        $deque = new Deque([2, 3]);

        $result = $deque->enqueueFront(1);

        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testEnqueueFrontReturnsSameInstance(): void
    {
        $deque = new Deque([]);

        $result = $deque->enqueueFront(1);

        $this->assertSame($deque, $result);
    }

    public function testEnqueueFrontUpdatesFrontAndCount(): void
    {
        $deque = new Deque([2, 3]);

        $deque->enqueueFront(1);

        $this->assertSame(1, $deque->front());
        $this->assertSame(3, $deque->count());
    }

    // --- dequeueTail ---

    public function testDequeueTailRemovesAndReturnsLastItem(): void
    {
        $deque = new Deque([1, 2, 3]);

        $value = $deque->dequeueTail();

        $this->assertSame(3, $value);
        $this->assertSame([1, 2], $deque->toArray());
    }

    public function testDequeueTailUpdatesRearAndCount(): void
    {
        $deque = new Deque([1, 2, 3]);

        $deque->dequeueTail();

        $this->assertSame(2, $deque->rear());
        $this->assertSame(2, $deque->count());
    }

    public function testDequeueTailOnSingleItemDequeEmptiesIt(): void
    {
        $deque = new Deque([1]);

        $value = $deque->dequeueTail();

        $this->assertSame(1, $value);
        $this->assertTrue($deque->isEmpty());
    }

    public function testDequeueTailOnEmptyDequeReturnsNull(): void
    {
        // Deque::dequeueTail() overrides Queue::dequeue() with array_pop(),
        // which returns null on an empty array instead of throwing.
        $deque = new Deque([]);

        $this->assertNull($deque->dequeueTail());
    }

    // --- combined front/rear usage (as a genuine double-ended queue) ---

    public function testActsAsDoubleEndedQueueAcrossBothEnds(): void
    {
        $deque = new Deque([]);
        $deque->setMaxCapacity(10);

        $deque->enqueue(2)->enqueue(3);
        $deque->enqueueFront(1);

        $this->assertSame([1, 2, 3], $deque->toArray());

        $this->assertSame(1, $deque->dequeue());
        $this->assertSame(3, $deque->dequeueTail());
        $this->assertSame([2], $deque->toArray());
    }

    // --- inherited Queue behaviour still applies ---

    public function testInheritedEnqueueStillThrowsWhenCapacityExceeded(): void
    {
        $deque = new Deque([]);
        $deque->setMaxCapacity(2);

        $deque->enqueue('a')->enqueue('b');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The queue is full.');

        $deque->enqueue('c');
    }

    public function testInheritedDequeueThrowsWhenEmpty(): void
    {
        $deque = new Deque([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Queue is empty');

        $deque->dequeue();
    }
}
