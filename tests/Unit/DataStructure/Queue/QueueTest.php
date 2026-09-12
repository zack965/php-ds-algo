<?php

namespace Tests\Unit\DataStructure\Queue;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Queue\Queue;

class QueueTest extends TestCase
{
    // --- Construction ---

    public function testConstructWithEmptyArrayIsEmpty(): void
    {
        $queue = new Queue([]);

        $this->assertTrue($queue->isEmpty());
        $this->assertSame(0, $queue->count());
        $this->assertSame([], $queue->toArray());
    }

    public function testConstructWithValuesPopulatesQueue(): void
    {
        $queue = new Queue([1, 2, 3]);

        $this->assertSame(3, $queue->count());
        $this->assertSame([1, 2, 3], $queue->toArray());
    }

    public function testConstructDoesNotReindexArrayKeys(): void
    {
        // Unlike ArrayStack::fromArray(), the constructor skips array_values(),
        // so non-sequential keys are preserved as-is.
        $queue = new Queue([5 => 'a', 9 => 'b']);

        $this->assertSame([5 => 'a', 9 => 'b'], $queue->toArray());
    }

    public function testConstructAcceptsMaxCapacityAsSecondArgument(): void
    {
        $queue = new Queue([1, 2], 5);

        $this->assertSame(5, $queue->getMaxCapacity());
    }

    public function testConstructAllowsInitialItemsExactlyAtMaxCapacity(): void
    {
        $queue = new Queue([1, 2], 2);

        $this->assertSame(2, $queue->count());
        $this->assertTrue($queue->isFull());
    }

    public function testConstructThrowsWhenInitialItemsExceedMaxCapacity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum capacity cannot be smaller than the number of initial items.');

        new Queue([1, 2, 3], 2);
    }

    // --- enqueue ---

    public function testEnqueueAddsItemToRear(): void
    {
        $queue = new Queue([1, 2]);

        $result = $queue->enqueue(3);

        $this->assertSame(3, $result->rear());
        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testEnqueueReturnsSameInstance(): void
    {
        $queue = new Queue([]);

        $result = $queue->enqueue(1);

        $this->assertSame($queue, $result);
    }

    public function testEnqueueSucceedsWithoutExplicitlySettingMaxCapacity(): void
    {
        // maxCapacity now defaults to PHP_INT_MAX, so a plain `new Queue()`
        // behaves as an effectively unbounded queue out of the box.
        $queue = new Queue([]);

        $queue->enqueue('a');

        $this->assertSame(['a'], $queue->toArray());
    }

    public function testEnqueueThrowsAssoonAsQueueReachesMaxCapacity(): void
    {
        // isFull() (and therefore enqueue()) now trips as soon as the queue
        // reaches capacity, with no more off-by-one overfill allowance.
        $queue = new Queue([]);
        $queue->setMaxCapacity(2);

        $queue->enqueue('a')->enqueue('b');

        $this->assertSame(2, $queue->count());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The queue is full.');

        $queue->enqueue('c');
    }

    // --- dequeue ---

    public function testDequeueRemovesAndReturnsFrontItem(): void
    {
        $queue = new Queue([1, 2, 3]);

        $value = $queue->dequeue();

        $this->assertSame(1, $value);
        $this->assertSame([2, 3], $queue->toArray());
        $this->assertSame(2, $queue->count());
    }

    public function testDequeueShiftsRemainingItemsToFront(): void
    {
        $queue = new Queue([1, 2, 3]);

        $queue->dequeue();

        $this->assertSame(2, $queue->front());
    }

    public function testDequeueThrowsWhenQueueEmpty(): void
    {
        $queue = new Queue([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The Queue is empty');

        $queue->dequeue();
    }

    // --- front / rear ---

    public function testFrontReturnsFirstItemWithoutRemovingIt(): void
    {
        $queue = new Queue([1, 2, 3]);

        $this->assertSame(1, $queue->front());
        $this->assertSame(3, $queue->count());
    }

    public function testRearReturnsLastItemWithoutRemovingIt(): void
    {
        $queue = new Queue([1, 2, 3]);

        $this->assertSame(3, $queue->rear());
        $this->assertSame(3, $queue->count());
    }

    // --- maxCapacity ---

    public function testSetMaxCapacityReturnsSameInstance(): void
    {
        $queue = new Queue([]);

        $result = $queue->setMaxCapacity(5);

        $this->assertSame($queue, $result);
    }

    public function testGetMaxCapacityReturnsConfiguredValue(): void
    {
        $queue = new Queue([]);
        $queue->setMaxCapacity(5);

        $this->assertSame(5, $queue->getMaxCapacity());
    }

    public function testGetMaxCapacityDefaultsToPhpIntMax(): void
    {
        $queue = new Queue([]);

        $this->assertSame(PHP_INT_MAX, $queue->getMaxCapacity());
    }

    public function testSetMaxCapacityThrowsWhenBelowCurrentQueueSize(): void
    {
        $queue = new Queue([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum capacity cannot be smaller than the current queue size.');

        $queue->setMaxCapacity(2);
    }

    public function testSetMaxCapacityAllowsValueExactlyAtCurrentQueueSize(): void
    {
        $queue = new Queue([1, 2, 3]);

        $queue->setMaxCapacity(3);

        $this->assertSame(3, $queue->getMaxCapacity());
        $this->assertTrue($queue->isFull());
    }

    // --- isEmpty ---

    public function testIsEmptyReturnsTrueForEmptyQueue(): void
    {
        $this->assertTrue((new Queue([]))->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyQueue(): void
    {
        $this->assertFalse((new Queue([1]))->isEmpty());
    }

    // --- clear ---

    public function testClearEmptiesTheQueue(): void
    {
        $queue = new Queue([1, 2, 3]);

        $result = $queue->clear();

        $this->assertTrue($result->isEmpty());
        $this->assertSame(0, $result->count());
    }

    public function testClearReturnsSameInstance(): void
    {
        $queue = new Queue([1, 2, 3]);

        $result = $queue->clear();

        $this->assertSame($queue, $result);
    }

    // --- toArray / toIterable ---

    public function testToArrayReturnsItemsFromFrontToRear(): void
    {
        $queue = new Queue([]);
        $queue->setMaxCapacity(10);
        $queue->enqueue(1)->enqueue(2)->enqueue(3);

        $this->assertSame([1, 2, 3], $queue->toArray());
    }

    public function testToArrayOnEmptyQueueReturnsEmptyArray(): void
    {
        $this->assertSame([], (new Queue([]))->toArray());
    }

    public function testToIterableYieldsItemsFromFrontToRear(): void
    {
        $queue = new Queue([1, 2, 3]);

        $values = [];
        foreach ($queue->toIterable() as $value) {
            $values[] = $value;
        }

        $this->assertSame([1, 2, 3], $values);
    }

    // --- isFull ---

    public function testIsFullReturnsFalseWhenBelowCapacity(): void
    {
        $queue = new Queue([1]);
        $queue->setMaxCapacity(5);

        $this->assertFalse($queue->isFull());
    }

    public function testIsFullReturnsTrueExactlyAtCapacity(): void
    {
        $queue = new Queue([]);
        $queue->setMaxCapacity(2);
        $queue->enqueue('a')->enqueue('b');

        $this->assertTrue($queue->isFull());
    }

    public function testIsFullReturnsFalseByDefaultWithoutSettingMaxCapacity(): void
    {
        // maxCapacity defaults to PHP_INT_MAX, so a plain queue is never
        // "full" for any reasonable size.
        $queue = new Queue([1, 2]);

        $this->assertFalse($queue->isFull());
    }

    // --- contains ---

    public function testContainsReturnsTrueWhenItemPresent(): void
    {
        $queue = new Queue([1, 2, 3]);

        $this->assertTrue($queue->contains(2));
    }

    public function testContainsReturnsFalseWhenItemAbsent(): void
    {
        $queue = new Queue([1, 2, 3]);

        $this->assertFalse($queue->contains(99));
    }

    public function testContainsUsesStrictComparison(): void
    {
        $queue = new Queue(['1', 2, 3]);

        $this->assertFalse($queue->contains(1));
    }

    public function testContainsOnEmptyQueueReturnsFalse(): void
    {
        $this->assertFalse((new Queue([]))->contains(1));
    }

    // --- count ---

    public function testCountReturnsNumberOfItems(): void
    {
        $queue = new Queue([1, 2, 3, 4]);

        $this->assertSame(4, $queue->count());
    }

    public function testCountOnEmptyQueueReturnsZero(): void
    {
        $this->assertSame(0, (new Queue([]))->count());
    }

    public function testCountFunctionWorksViaCountableInterface(): void
    {
        $queue = new Queue([1, 2, 3]);

        $this->assertCount(3, $queue);
    }
}
