<?php

namespace Tests\Unit\Helpers\Algorythmes;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class AlgorythmesGlobalHelpersTest extends TestCase
{
    // --- isBetween ---

    public function testIsBetweenReturnsTrueWhenWithinBounds(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isBetween(5, 1, 10));
    }

    public function testIsBetweenReturnsTrueAtLowerBound(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isBetween(1, 1, 10));
    }

    public function testIsBetweenReturnsTrueAtUpperBound(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isBetween(10, 1, 10));
    }

    public function testIsBetweenReturnsFalseBelowLowerBound(): void
    {
        $this->assertFalse(AlgorythmesGlobalHelpers::isBetween(0, 1, 10));
    }

    public function testIsBetweenReturnsFalseAboveUpperBound(): void
    {
        $this->assertFalse(AlgorythmesGlobalHelpers::isBetween(11, 1, 10));
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

    // --- InsertCorrectly ---

    public function testInsertCorrectlyIsCurrentlyAnEmptyStub(): void
    {
        // No implementation exists yet — this pins down the current no-op
        // behavior (returns null, leaves the array untouched) so a real
        // implementation later will visibly change this test.
        $nums = [1, 2, 3];

        $result = AlgorythmesGlobalHelpers::InsertCorrectly($nums, 4);

        $this->assertNull($result);
        $this->assertSame([1, 2, 3], $nums);
    }
}
