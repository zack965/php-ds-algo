<?php

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class SortingAlgorithmsTest extends TestCase
{
    // --- selectionSort ---

    public function testSelectionSortSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::selectionSort([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testSelectionSortHandlesAlreadySortedArray(): void
    {
        $result = ArraySortAlgorythmes::selectionSort([1, 2, 3, 4, 5]);

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

    // --- swapValuesOfArray ---

    public function testSwapValuesOfArraySwapsTwoIndexes(): void
    {
        $nums = [1, 2, 3];

        AlgorythmesGlobalHelpers::swapValuesOfArray($nums, 0, 2);

        $this->assertSame([3, 2, 1], $nums);
    }

    public function testSwapValuesOfArrayWithSameIndexIsNoOp(): void
    {
        $nums = [1, 2, 3];

        AlgorythmesGlobalHelpers::swapValuesOfArray($nums, 1, 1);

        $this->assertSame([1, 2, 3], $nums);
    }

    public function testSwapValuesOfArrayThrowsWhenStartIndexMissing(): void
    {
        $nums = [1, 2, 3];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Start index 99 does not exist in the array.');

        AlgorythmesGlobalHelpers::swapValuesOfArray($nums, 99, 0);
    }

    public function testSwapValuesOfArrayThrowsWhenEndIndexMissing(): void
    {
        $nums = [1, 2, 3];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End index 99 does not exist in the array.');

        AlgorythmesGlobalHelpers::swapValuesOfArray($nums, 0, 99);
    }
}
