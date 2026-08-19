<?php

namespace Tests\Unit\DataStructure\Heap;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\Fixtures\Task;
use Tests\Support\HeapInvariantAssertions;
use Zack\PhpDsAlgo\DataStructure\Heap\MaxHeap;

class MaxHeapTest extends TestCase
{
    use HeapInvariantAssertions;

    private function defaultComparator(): callable
    {
        return fn($a, $b) => $b <=> $a;
    }

    // --- Construction ---

    public function testConstructWithEmptyArrayIsEmpty(): void
    {
        $heap = new MaxHeap([]);

        $this->assertTrue($heap->isEmpty());
        $this->assertSame(0, $heap->size());
    }

    public function testConstructWithSingleElementSucceeds(): void
    {
        $heap = new MaxHeap([42]);

        $this->assertSame(1, $heap->size());
        $this->assertSame(42, $heap->peek());
    }

    public function testConstructWithUnorderedArrayHeapifiesAndPutsMaximumAtRoot(): void
    {
        $heap = new MaxHeap([5, 3, 8, 1, 9, 2, 7]);

        $this->assertSame(9, $heap->peek());
        $this->assertSame(7, $heap->size());
        $this->assertHeapProperty($heap->toArray(), $this->defaultComparator());
    }

    public function testConstructWithDuplicateMaximumValuesSucceeds(): void
    {
        $heap = new MaxHeap([3, 3, 2, 1]);

        $this->assertSame(3, $heap->peek());
        $this->assertHeapProperty($heap->toArray(), $this->defaultComparator());
    }

    public function testConstructWithNegativeAndPositiveNumbersPutsLargestAtRoot(): void
    {
        $heap = new MaxHeap([3, -5, 10, -20, 0, 7]);

        $this->assertSame(10, $heap->peek());
        $this->assertHeapProperty($heap->toArray(), $this->defaultComparator());
    }

    public function testConstructWithExplicitCapacitySetsCapacity(): void
    {
        $heap = new MaxHeap([], 64);

        $this->assertSame(64, $heap->getCapacity());
    }

    public function testConstructWithCapacitySmallerThanDataGrowsToFitData(): void
    {
        $heap = new MaxHeap([1, 2, 3, 4, 5], 2);

        $this->assertGreaterThanOrEqual(5, $heap->getCapacity());
        $this->assertSame(5, $heap->size());
    }

    // --- insert ---

    public function testInsertIntoEmptyHeapSetsSingleElement(): void
    {
        $heap = new MaxHeap([]);

        $heap->insert(10);

        $this->assertSame(1, $heap->size());
        $this->assertSame(10, $heap->peek());
    }

    public function testInsertSmallerValueKeepsExistingRoot(): void
    {
        $heap = new MaxHeap([5, 3, 4]);

        $heap->insert(1);

        $this->assertSame(5, $heap->peek());
        $this->assertSame(4, $heap->size());
    }

    public function testInsertLargerValueBubblesUpToRoot(): void
    {
        $heap = new MaxHeap([5, 3, 4]);

        $heap->insert(9);

        $this->assertSame(9, $heap->peek());
    }

    public function testInsertMaintainsMaxAtRootAcrossManyInserts(): void
    {
        $heap = new MaxHeap([]);

        foreach ([5, 3, 8, 1, 9, 2, 7] as $value) {
            $heap->insert($value);
            $this->assertHeapProperty($heap->toArray(), $this->defaultComparator());
        }

        $this->assertSame(9, $heap->peek());
        $this->assertSame(7, $heap->size());
    }

    public function testInsertGrowsCapacityBeyondDefault(): void
    {
        $heap = new MaxHeap([]);
        $initialCapacity = $heap->getCapacity();

        for ($i = 0; $i < $initialCapacity + 5; $i++) {
            $heap->insert($i);
        }

        $this->assertGreaterThan($initialCapacity, $heap->getCapacity());
        $this->assertSame($initialCapacity + 5, $heap->size());
        $this->assertSame($initialCapacity + 4, $heap->peek());
    }

    // --- extract ---

    public function testExtractThrowsWhenHeapEmpty(): void
    {
        $heap = new MaxHeap([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Heap is empty');

        $heap->extract();
    }

    public function testExtractOnSingleElementHeapEmptiesIt(): void
    {
        $heap = new MaxHeap([42]);

        $value = $heap->extract();

        $this->assertSame(42, $value);
        $this->assertTrue($heap->isEmpty());
    }

    public function testExtractReturnsAndRemovesTheMaximum(): void
    {
        $heap = new MaxHeap([1, 3, 2, 5, 4]);

        $value = $heap->extract();

        $this->assertSame(5, $value);
        $this->assertSame(4, $heap->size());
    }

    public function testExtractRestoresHeapPropertyAfterRemoval(): void
    {
        $heap = new MaxHeap([1, 3, 2, 5, 4]);

        $heap->extract();

        $this->assertSame(4, $heap->peek());
        $this->assertHeapProperty($heap->toArray(), $this->defaultComparator());
    }

    public function testRepeatedExtractReturnsValuesInDescendingOrder(): void
    {
        $heap = new MaxHeap([]);
        foreach ([5, 3, 8, 1, 9, 2, 7] as $value) {
            $heap->insert($value);
        }

        $sorted = [];
        while (!$heap->isEmpty()) {
            $sorted[] = $heap->extract();
        }

        $this->assertSame([9, 8, 7, 5, 3, 2, 1], $sorted);
    }

    public function testRepeatedExtractFromArrayBuiltHeapReturnsDescendingOrder(): void
    {
        $heap = new MaxHeap([9, 4, 7, 1, 8, 2, 6, 3, 5, 0]);

        $sorted = [];
        while (!$heap->isEmpty()) {
            $sorted[] = $heap->extract();
        }

        $this->assertSame(range(9, 0, -1), $sorted);
    }

    public function testExtractAfterExhaustingHeapThrows(): void
    {
        $heap = new MaxHeap([1, 2]);
        $heap->extract();
        $heap->extract();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Heap is empty');

        $heap->extract();
    }

    // --- peek ---

    public function testPeekReturnsMaximumWithoutRemovingIt(): void
    {
        $heap = new MaxHeap([1, 3, 2]);

        $value = $heap->peek();

        $this->assertSame(3, $value);
        $this->assertSame(3, $heap->size());
    }

    public function testPeekThrowsWhenHeapEmpty(): void
    {
        $heap = new MaxHeap([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Heap is empty');

        $heap->peek();
    }

    // --- isEmpty / size ---

    public function testIsEmptyReturnsTrueForEmptyHeap(): void
    {
        $this->assertTrue((new MaxHeap([]))->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyHeap(): void
    {
        $this->assertFalse((new MaxHeap([1]))->isEmpty());
    }

    public function testSizeReturnsNumberOfElements(): void
    {
        $heap = new MaxHeap([1, 2, 3, 4]);

        $this->assertSame(4, $heap->size());
    }

    public function testSizeOnEmptyHeapReturnsZero(): void
    {
        $this->assertSame(0, (new MaxHeap([]))->size());
    }

    // --- capacity ---

    public function testEnsureCapacityGrowsWhenRequestedAboveCurrent(): void
    {
        $heap = new MaxHeap([], 4);

        $heap->ensureCapacity(10);

        $this->assertGreaterThanOrEqual(10, $heap->getCapacity());
    }

    public function testEnsureCapacityIsNoOpWhenRequestedBelowCurrent(): void
    {
        $heap = new MaxHeap([], 32);

        $heap->ensureCapacity(4);

        $this->assertSame(32, $heap->getCapacity());
    }

    // --- clear ---

    public function testClearEmptiesTheHeap(): void
    {
        $heap = new MaxHeap([1, 2, 3]);

        $heap->clear();

        $this->assertTrue($heap->isEmpty());
        $this->assertSame(0, $heap->size());
    }

    public function testClearPreservesCapacity(): void
    {
        $heap = new MaxHeap([1, 2, 3], 64);

        $heap->clear();

        $this->assertSame(64, $heap->getCapacity());
    }

    public function testHeapIsUsableAfterClear(): void
    {
        $heap = new MaxHeap([1, 2, 3]);
        $heap->clear();

        $heap->insert(4);
        $heap->insert(9);

        $this->assertSame(9, $heap->peek());
        $this->assertSame(2, $heap->size());
    }

    // --- toArray ---

    public function testToArrayReturnsExactlySizeElements(): void
    {
        $heap = new MaxHeap([3, 1, 2], 16);

        $this->assertCount(3, $heap->toArray());
    }

    public function testToArrayContainsAllInsertedValues(): void
    {
        $values = [5, 3, 8, 1, 9, 2, 7];
        $heap = new MaxHeap($values);

        $this->assertEqualsCanonicalizing($values, $heap->toArray());
    }

    // --- isValid ---

    public function testIsValidIsTrueForEmptyHeap(): void
    {
        $this->assertTrue((new MaxHeap([]))->isValid());
    }

    public function testIsValidIsTrueForSingleElementHeap(): void
    {
        $this->assertTrue((new MaxHeap([1]))->isValid());
    }

    public function testIsValidIsTrueForAProperlyOrderedMultiElementHeap(): void
    {
        $heap = new MaxHeap([9, 3, 8, 1, 5, 2, 4]);

        $this->assertTrue($heap->isValid());
    }

    // --- custom comparator: scalars, ascending order (inverted MaxHeap) ---

    public function testCustomAscendingComparatorPutsSmallestAtRootOnInsert(): void
    {
        $ascending = fn($a, $b) => $a <=> $b;
        $heap = new MaxHeap([], 0, $ascending);

        foreach ([9, 5, 3, 1, 2, 8, 4] as $value) {
            $heap->insert($value);
        }

        $this->assertSame(1, $heap->peek());
        $this->assertHeapProperty($heap->toArray(), $ascending);
    }

    public function testCustomAscendingComparatorBuildsValidHeapFromArray(): void
    {
        $ascending = fn($a, $b) => $a <=> $b;
        $heap = new MaxHeap([1, 3, 2, 5, 4], 0, $ascending);

        $this->assertHeapProperty($heap->toArray(), $ascending);
        $this->assertSame(1, $heap->peek());
    }

    public function testCustomAscendingComparatorExtractsInAscendingOrder(): void
    {
        $ascending = fn($a, $b) => $a <=> $b;
        $heap = new MaxHeap([1, 3, 2, 5, 4], 0, $ascending);

        $order = [];
        while (!$heap->isEmpty()) {
            $order[] = $heap->extract();
        }

        $this->assertSame([1, 2, 3, 4, 5], $order);
    }

    // --- custom comparator: object payloads (priority queue use case) ---

    public function testCustomComparatorOnObjectsExtractsInDescendingPriorityOrderInsertBuilt(): void
    {
        $byPriority = fn(Task $a, Task $b) => $b->priority <=> $a->priority;
        $heap = new MaxHeap([], 0, $byPriority);

        foreach (
            [
                new Task('alpha', 9),
                new Task('bravo', 3),
                new Task('charlie', 7),
                new Task('delta', 1),
                new Task('echo', 5),
                new Task('foxtrot', 2),
                new Task('golf', 8),
            ] as $task
        ) {
            $heap->insert($task);
        }

        $priorities = [];
        while (!$heap->isEmpty()) {
            $priorities[] = $heap->extract()->priority;
        }

        $this->assertSame([9, 8, 7, 5, 3, 2, 1], $priorities);
    }

    public function testCustomComparatorOnObjectsExtractsInDescendingPriorityOrderArrayBuilt(): void
    {
        $byPriority = fn(Task $a, Task $b) => $b->priority <=> $a->priority;
        $tasks = [
            new Task('alpha', 9),
            new Task('bravo', 3),
            new Task('charlie', 7),
            new Task('delta', 1),
            new Task('echo', 5),
            new Task('foxtrot', 2),
            new Task('golf', 8),
        ];
        $heap = new MaxHeap($tasks, 0, $byPriority);

        $priorities = [];
        while (!$heap->isEmpty()) {
            $priorities[] = $heap->extract()->priority;
        }

        $this->assertSame([9, 8, 7, 5, 3, 2, 1], $priorities);
    }

    public function testCustomComparatorOnObjectsMaintainsHeapPropertyAfterExtract(): void
    {
        $byPriority = fn(Task $a, Task $b) => $b->priority <=> $a->priority;
        $tasks = [
            new Task('alpha', 9),
            new Task('bravo', 3),
            new Task('charlie', 7),
            new Task('delta', 1),
            new Task('echo', 5),
        ];
        $heap = new MaxHeap($tasks, 0, $byPriority);

        $heap->extract();

        $this->assertHeapProperty($heap->toArray(), $byPriority);
    }

    // --- custom comparator: string length ---

    public function testCustomComparatorByStringLengthPutsLongestAtRoot(): void
    {
        $byLength = fn(string $a, string $b) => strlen($b) <=> strlen($a);
        $words = ['banana', 'fig', 'kiwi', 'a', 'watermelon', 'pear'];
        $heap = new MaxHeap($words, 0, $byLength);

        $this->assertSame('watermelon', $heap->peek());
        $this->assertHeapProperty($heap->toArray(), $byLength);
    }

    public function testCustomComparatorByStringLengthExtractsInDescendingLengthOrder(): void
    {
        $byLength = fn(string $a, string $b) => strlen($b) <=> strlen($a);
        $words = ['banana', 'fig', 'kiwi', 'a', 'watermelon', 'pear'];
        $heap = new MaxHeap($words, 0, $byLength);

        $lengths = [];
        while (!$heap->isEmpty()) {
            $lengths[] = strlen($heap->extract());
        }

        $sortedLengths = array_map('strlen', $words);
        rsort($sortedLengths);

        $this->assertSame($sortedLengths, $lengths);
    }
}
