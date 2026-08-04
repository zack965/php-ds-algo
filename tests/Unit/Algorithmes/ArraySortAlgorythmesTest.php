<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes;

class ArraySortAlgorythmesTest extends TestCase
{
    // --- bubbleSort ---

    public function testBubbleSortSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::bubbleSort([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testBubbleSortHandlesAlreadySortedArray(): void
    {
        $result = ArraySortAlgorythmes::bubbleSort([1, 2, 3, 4, 5]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testBubbleSortHandlesReverseSortedArray(): void
    {
        $result = ArraySortAlgorythmes::bubbleSort([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testBubbleSortHandlesDuplicateValues(): void
    {
        $result = ArraySortAlgorythmes::bubbleSort([3, 1, 2, 1, 3]);

        $this->assertSame([1, 1, 2, 3, 3], $result);
    }

    public function testBubbleSortHandlesEmptyArray(): void
    {
        $this->assertSame([], ArraySortAlgorythmes::bubbleSort([]));
    }

    public function testBubbleSortHandlesSingleElementArray(): void
    {
        $this->assertSame([42], ArraySortAlgorythmes::bubbleSort([42]));
    }

    public function testBubbleSortDoesNotMutateInputArray(): void
    {
        $input = [3, 1, 2];

        ArraySortAlgorythmes::bubbleSort($input);

        $this->assertSame([3, 1, 2], $input);
    }

    // --- selectionSort ---

    public function testSelectionSortSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::selectionSort([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testSelectionSortHandlesReverseSortedArray(): void
    {
        $result = ArraySortAlgorythmes::selectionSort([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testSelectionSortHandlesDuplicateValues(): void
    {
        $result = ArraySortAlgorythmes::selectionSort([3, 1, 2, 1, 3]);

        $this->assertSame([1, 1, 2, 3, 3], $result);
    }

    public function testSelectionSortHandlesEmptyArray(): void
    {
        $this->assertSame([], ArraySortAlgorythmes::selectionSort([]));
    }

    public function testSelectionSortHandlesSingleElementArray(): void
    {
        $this->assertSame([42], ArraySortAlgorythmes::selectionSort([42]));
    }

    public function testSelectionSortDoesNotMutateInputArray(): void
    {
        $input = [3, 1, 2];

        ArraySortAlgorythmes::selectionSort($input);

        $this->assertSame([3, 1, 2], $input);
    }

    // --- insertionSort ---

    public function testInsertionSortSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::insertionSort([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testInsertionSortHandlesReverseSortedArray(): void
    {
        $result = ArraySortAlgorythmes::insertionSort([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testInsertionSortHandlesDuplicateValues(): void
    {
        $result = ArraySortAlgorythmes::insertionSort([3, 1, 2, 1, 3]);

        $this->assertSame([1, 1, 2, 3, 3], $result);
    }

    public function testInsertionSortHandlesEmptyArray(): void
    {
        $this->assertSame([], ArraySortAlgorythmes::insertionSort([]));
    }

    public function testInsertionSortHandlesSingleElementArray(): void
    {
        $this->assertSame([42], ArraySortAlgorythmes::insertionSort([42]));
    }

    public function testInsertionSortDoesNotMutateInputArray(): void
    {
        $input = [3, 1, 2];

        ArraySortAlgorythmes::insertionSort($input);

        $this->assertSame([3, 1, 2], $input);
    }

    // --- MergeSort ---

    public function testMergeSortSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::MergeSort([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testMergeSortHandlesAlreadySortedArray(): void
    {
        $result = ArraySortAlgorythmes::MergeSort([1, 2, 3, 4, 5]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testMergeSortHandlesReverseSortedArray(): void
    {
        $result = ArraySortAlgorythmes::MergeSort([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testMergeSortHandlesDuplicateValues(): void
    {
        $result = ArraySortAlgorythmes::MergeSort([3, 1, 2, 1, 3]);

        $this->assertSame([1, 1, 2, 3, 3], $result);
    }

    public function testMergeSortHandlesEmptyArray(): void
    {
        $this->assertSame([], ArraySortAlgorythmes::MergeSort([]));
    }

    public function testMergeSortHandlesSingleElementArray(): void
    {
        $this->assertSame([42], ArraySortAlgorythmes::MergeSort([42]));
    }

    public function testMergeSortHandlesTwoElementArray(): void
    {
        $this->assertSame([1, 2], ArraySortAlgorythmes::MergeSort([2, 1]));
    }

    public function testMergeSortHandlesNegativeNumbers(): void
    {
        $result = ArraySortAlgorythmes::MergeSort([0, -3, 5, -1, 2]);

        $this->assertSame([-3, -1, 0, 2, 5], $result);
    }

    public function testMergeSortDoesNotMutateInputArray(): void
    {
        $input = [3, 1, 2];

        ArraySortAlgorythmes::MergeSort($input);

        $this->assertSame([3, 1, 2], $input);
    }

    // --- QuickSOrt ---

    public function testQuickSOrtSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::QuickSOrt([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testQuickSOrtHandlesAlreadySortedArray(): void
    {
        $result = ArraySortAlgorythmes::QuickSOrt([1, 2, 3, 4, 5]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testQuickSOrtHandlesReverseSortedArray(): void
    {
        $result = ArraySortAlgorythmes::QuickSOrt([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testQuickSOrtHandlesDuplicateValues(): void
    {
        $result = ArraySortAlgorythmes::QuickSOrt([3, 1, 2, 1, 3]);

        $this->assertSame([1, 1, 2, 3, 3], $result);
    }

    public function testQuickSOrtHandlesEmptyArray(): void
    {
        $this->assertSame([], ArraySortAlgorythmes::QuickSOrt([]));
    }

    public function testQuickSOrtHandlesSingleElementArray(): void
    {
        $this->assertSame([42], ArraySortAlgorythmes::QuickSOrt([42]));
    }

    public function testQuickSOrtHandlesTwoElementArray(): void
    {
        $this->assertSame([1, 2], ArraySortAlgorythmes::QuickSOrt([2, 1]));
    }

    public function testQuickSOrtHandlesNegativeNumbers(): void
    {
        $result = ArraySortAlgorythmes::QuickSOrt([0, -3, 5, -1, 2]);

        $this->assertSame([-3, -1, 0, 2, 5], $result);
    }
}
