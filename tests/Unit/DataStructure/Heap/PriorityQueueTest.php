<?php

namespace Tests\Unit\DataStructure\Heap;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueue;
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueueNode;
use Zack\PhpDsAlgo\enums\PriorityQueueTypeEnum;

class PriorityQueueTest extends TestCase
{
    // --- construction ---

    public function testNewQueueIsEmpty(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $this->assertTrue($queue->isEmpty());
        $this->assertSame(0, $queue->size());
    }

    // --- insert ---

    public function testInsertIntoEmptyQueueSetsSingleElement(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $queue->insert('task-a', 5);

        $this->assertSame(1, $queue->size());
        $this->assertFalse($queue->isEmpty());
    }

    public function testPeekAfterInsertReturnsNodeWithValueAndPriority(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $queue->insert('task-a', 5);
        $node = $queue->peek();

        $this->assertInstanceOf(PriorityQueueNode::class, $node);
        $this->assertSame('task-a', $node->getValue());
        $this->assertSame(5, $node->getPriority());
    }

    public function testInsertHigherPriorityBubblesToFront(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $queue->insert('low', 1);
        $queue->insert('high', 9);
        $queue->insert('medium', 5);

        $this->assertSame('high', $queue->peek()->getValue());
        $this->assertSame(3, $queue->size());
    }

    public function testInsertLowerPriorityKeepsExistingFront(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $queue->insert('high', 9);
        $queue->insert('low', 1);

        $this->assertSame('high', $queue->peek()->getValue());
    }

    public function testInsertAcceptsFloatPriorities(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $queue->insert('a', 1.5);
        $queue->insert('b', 2.7);

        $this->assertSame('b', $queue->peek()->getValue());
    }

    public function testInsertMaintainsHighestPriorityAtFrontAcrossManyInserts(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        foreach ([['v' => 'e', 'p' => 5], ['v' => 'c', 'p' => 8], ['v' => 'a', 'p' => 1], ['v' => 'd', 'p' => 9], ['v' => 'b', 'p' => 2]] as $item) {
            $queue->insert($item['v'], $item['p']);
        }

        $this->assertSame('d', $queue->peek()->getValue());
        $this->assertSame(5, $queue->size());
    }

    // --- insertMany ---

    public function testInsertManyAddsAllNodes(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $queue->insertMany([
            new PriorityQueueNode('a', 1),
            new PriorityQueueNode('b', 3),
            new PriorityQueueNode('c', 2),
        ]);

        $this->assertSame(3, $queue->size());
        $this->assertSame('b', $queue->peek()->getValue());
    }

    public function testInsertManyOnEmptyArrayIsNoOp(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $queue->insertMany([]);

        $this->assertTrue($queue->isEmpty());
    }

    public function testInsertManyAppendsToExistingNodes(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('existing', 4);

        $queue->insertMany([
            new PriorityQueueNode('a', 1),
            new PriorityQueueNode('b', 9),
        ]);

        $this->assertSame(3, $queue->size());
        $this->assertSame('b', $queue->peek()->getValue());
    }

    // --- peek ---

    public function testPeekDoesNotRemoveElement(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('only', 1);

        $queue->peek();

        $this->assertSame(1, $queue->size());
    }

    public function testPeekThrowsWhenQueueEmpty(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Heap is empty');

        $queue->peek();
    }

    // --- extract ---

    public function testExtractThrowsWhenQueueEmpty(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Heap is empty');

        $queue->extract();
    }

    public function testExtractReturnsAndRemovesHighestPriorityNode(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('low', 1);
        $queue->insert('high', 9);
        $queue->insert('medium', 5);

        $node = $queue->extract();

        $this->assertSame('high', $node->getValue());
        $this->assertSame(9, $node->getPriority());
        $this->assertSame(2, $queue->size());
    }

    public function testExtractOnSingleElementQueueEmptiesIt(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('only', 1);

        $queue->extract();

        $this->assertTrue($queue->isEmpty());
    }

    public function testRepeatedExtractReturnsValuesInDescendingPriorityOrder(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        foreach (['e' => 5, 'c' => 8, 'a' => 1, 'd' => 9, 'b' => 2] as $value => $priority) {
            $queue->insert($value, $priority);
        }

        $order = [];
        while (!$queue->isEmpty()) {
            $order[] = $queue->extract()->getValue();
        }

        $this->assertSame(['d', 'c', 'e', 'b', 'a'], $order);
    }

    public function testExtractPreservesNonIncreasingPriorityOrderWithTies(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('a', 5);
        $queue->insert('b', 3);
        $queue->insert('c', 5);
        $queue->insert('d', 1);
        $queue->insert('e', 3);

        $priorities = [];
        while (!$queue->isEmpty()) {
            $priorities[] = $queue->extract()->getPriority();
        }

        $sorted = $priorities;
        rsort($sorted);
        $this->assertSame($sorted, $priorities);
    }

    public function testExtractAfterExhaustingQueueThrows(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('a', 1);
        $queue->insert('b', 2);
        $queue->extract();
        $queue->extract();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Heap is empty');

        $queue->extract();
    }

    // --- isEmpty / size ---

    public function testIsEmptyReturnsFalseAfterInsert(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('a', 1);

        $this->assertFalse($queue->isEmpty());
    }

    public function testSizeTracksNumberOfElements(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('a', 1);
        $queue->insert('b', 2);
        $queue->insert('c', 3);

        $this->assertSame(3, $queue->size());
    }

    // --- clear ---

    public function testClearEmptiesTheQueue(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('a', 1);
        $queue->insert('b', 2);

        $queue->clear();

        $this->assertTrue($queue->isEmpty());
        $this->assertSame(0, $queue->size());
    }

    public function testQueueIsUsableAfterClear(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max);
        $queue->insert('a', 1);
        $queue->clear();

        $queue->insert('b', 9);
        $queue->insert('c', 4);

        $this->assertSame('b', $queue->peek()->getValue());
        $this->assertSame(2, $queue->size());
    }

    // --- capacity growth ---

    public function testQueueGrowsBeyondInitialCapacity(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Max, 2);

        for ($i = 0; $i < 10; $i++) {
            $queue->insert("item-$i", $i);
        }

        $this->assertSame(10, $queue->size());
        $this->assertSame('item-9', $queue->peek()->getValue());
    }

    // --- Min type ---

    public function testNewMinQueueIsEmpty(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min);

        $this->assertTrue($queue->isEmpty());
        $this->assertSame(0, $queue->size());
    }

    public function testMinQueuePeekReturnsLowestPriorityNode(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min);

        $queue->insert('low', 1);
        $queue->insert('high', 9);
        $queue->insert('medium', 5);

        $this->assertSame('low', $queue->peek()->getValue());
        $this->assertSame(3, $queue->size());
    }

    public function testMinQueueInsertHigherPriorityKeepsExistingFront(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min);

        $queue->insert('low', 1);
        $queue->insert('high', 9);

        $this->assertSame('low', $queue->peek()->getValue());
    }

    public function testMinQueueInsertManyAddsAllNodes(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min);

        $queue->insertMany([
            new PriorityQueueNode('a', 1),
            new PriorityQueueNode('b', 3),
            new PriorityQueueNode('c', 2),
        ]);

        $this->assertSame(3, $queue->size());
        $this->assertSame('a', $queue->peek()->getValue());
    }

    public function testMinQueueExtractReturnsAndRemovesLowestPriorityNode(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min);
        $queue->insert('low', 1);
        $queue->insert('high', 9);
        $queue->insert('medium', 5);

        $node = $queue->extract();

        $this->assertSame('low', $node->getValue());
        $this->assertSame(1, $node->getPriority());
        $this->assertSame(2, $queue->size());
    }

    public function testRepeatedExtractOnMinQueueReturnsValuesInAscendingPriorityOrder(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min);
        foreach (['e' => 5, 'c' => 8, 'a' => 1, 'd' => 9, 'b' => 2] as $value => $priority) {
            $queue->insert($value, $priority);
        }

        $order = [];
        while (!$queue->isEmpty()) {
            $order[] = $queue->extract()->getValue();
        }

        $this->assertSame(['a', 'b', 'e', 'c', 'd'], $order);
    }

    public function testMinQueuePeekThrowsWhenQueueEmpty(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Heap is empty');

        $queue->peek();
    }

    public function testMinQueueGrowsBeyondInitialCapacity(): void
    {
        $queue = new PriorityQueue(PriorityQueueTypeEnum::Min, 2);

        for ($i = 9; $i >= 0; $i--) {
            $queue->insert("item-$i", $i);
        }

        $this->assertSame(10, $queue->size());
        $this->assertSame('item-0', $queue->peek()->getValue());
    }
}
