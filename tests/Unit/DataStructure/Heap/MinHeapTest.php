<?php

namespace Tests\Unit\DataStructure\Heap;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zack\PhpDsAlgo\DataStructure\Heap\MinHeap;

class MinHeapTest extends TestCase
{
    // --- Construction ---

    public function testConstructWithEmptyArrayIsEmpty(): void
    {
        // checker() indexes $this->data[0] unconditionally, which triggers a
        // PHP warning (not an exception) on an empty array before the
        // validation loop runs zero times.
        $heap = new MinHeap([]);

        $this->assertTrue($heap->isEmpty());
        $this->assertSame(0, $heap->size());
    }

    public function testConstructWithSingleElementSucceeds(): void
    {
        $heap = new MinHeap([42]);

        $this->assertSame(1, $heap->size());
        $this->assertSame(42, $heap->peek());
    }

    public function testConstructWithRootAsMinimumSucceeds(): void
    {
        // checker() only verifies that index 0 is <= every other element; it
        // does not validate the full heap shape/parent-child invariant.
        $heap = new MinHeap([1, 3, 2, 5, 4]);

        $this->assertSame([1, 3, 2, 5, 4], $heap->getData());
        $this->assertSame(1, $heap->peek());
    }

    public function testConstructWithDuplicateMinimumValuesSucceeds(): void
    {
        $heap = new MinHeap([1, 1, 2, 3]);

        $this->assertSame(1, $heap->peek());
    }

    public function testConstructThrowsWhenRootIsNotTheMinimum(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unordered Data');

        new MinHeap([5, 3, 4, 1, 0]);
    }

    // --- insert ---

    public function testInsertIntoEmptyHeapSetsSingleElement(): void
    {
        $heap = new MinHeap([]);

        $heap->insert(10);

        $this->assertSame(1, $heap->size());
        $this->assertSame(10, $heap->peek());
    }

    public function testInsertLargerValueKeepsExistingRoot(): void
    {
        $heap = new MinHeap([1, 3, 2]);

        $heap->insert(5);

        $this->assertSame(1, $heap->peek());
        $this->assertSame(4, $heap->size());
    }

    public function testInsertSmallerValueBubblesUpToRoot(): void
    {
        $heap = new MinHeap([5, 8, 6]);

        $heap->insert(1);

        $this->assertSame(1, $heap->peek());
    }

    public function testInsertSmallestValueBubblesThroughMultipleLevels(): void
    {
        $heap = new MinHeap([]);
        foreach ([10, 20, 15, 30, 25, 18] as $value) {
            $heap->insert($value);
        }

        $heap->insert(1);

        $this->assertSame(1, $heap->peek());
        $this->assertSame(7, $heap->size());
    }

    public function testInsertMaintainsMinAtRootAcrossManyInserts(): void
    {
        $heap = new MinHeap([]);

        foreach ([5, 3, 8, 1, 9, 2, 7] as $value) {
            $heap->insert($value);
        }

        $this->assertSame(1, $heap->peek());
        $this->assertSame(7, $heap->size());
    }

    // --- extract ---

    public function testExtractThrowsWhenHeapEmpty(): void
    {
        $heap = new MinHeap([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The heap is empty');

        $heap->extract();
    }

    public function testExtractOnSingleElementHeapEmptiesIt(): void
    {
        $heap = new MinHeap([42]);

        $value = $heap->extract();

        $this->assertSame(42, $value);
        $this->assertTrue($heap->isEmpty());
    }

    public function testExtractReturnsAndRemovesTheMinimum(): void
    {
        $heap = new MinHeap([1, 3, 2, 5, 4]);

        $value = $heap->extract();

        $this->assertSame(1, $value);
        $this->assertSame(4, $heap->size());
    }

    public function testExtractRestoresHeapPropertyAfterRemoval(): void
    {
        $heap = new MinHeap([1, 3, 2, 5, 4]);

        $heap->extract();

        // The next smallest value must now be at the root.
        $this->assertSame(2, $heap->peek());
    }

    public function testRepeatedExtractReturnsValuesInAscendingOrder(): void
    {
        $heap = new MinHeap([]);
        foreach ([5, 3, 8, 1, 9, 2, 7] as $value) {
            $heap->insert($value);
        }

        $sorted = [];
        while (!$heap->isEmpty()) {
            $sorted[] = $heap->extract();
        }

        $this->assertSame([1, 2, 3, 5, 7, 8, 9], $sorted);
    }

    public function testExtractAfterExhaustingHeapThrows(): void
    {
        $heap = new MinHeap([1, 2]);
        $heap->extract();
        $heap->extract();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The heap is empty');

        $heap->extract();
    }

    // --- peek ---

    public function testPeekReturnsMinimumWithoutRemovingIt(): void
    {
        $heap = new MinHeap([1, 3, 2]);

        $value = $heap->peek();

        $this->assertSame(1, $value);
        $this->assertSame(3, $heap->size());
    }

    public function testPeekThrowsWhenHeapEmpty(): void
    {
        $heap = new MinHeap([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The heap is empty');

        $heap->peek();
    }

    // --- isEmpty ---

    public function testIsEmptyReturnsTrueForEmptyHeap(): void
    {
        $this->assertTrue((new MinHeap([]))->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyHeap(): void
    {
        $this->assertFalse((new MinHeap([1]))->isEmpty());
    }

    // --- size ---

    public function testSizeReturnsNumberOfElements(): void
    {
        $heap = new MinHeap([1, 2, 3, 4]);

        $this->assertSame(4, $heap->size());
    }

    public function testSizeOnEmptyHeapReturnsZero(): void
    {
        $this->assertSame(0, (new MinHeap([]))->size());
    }

    // --- getData ---

    public function testGetDataReturnsInternalArray(): void
    {
        $heap = new MinHeap([1, 3, 2]);

        $this->assertSame([1, 3, 2], $heap->getData());
    }
}
