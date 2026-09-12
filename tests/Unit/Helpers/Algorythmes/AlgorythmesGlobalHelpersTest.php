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

    // --- isOdd / isEven ---

    public function testIsOddReturnsTrueForOddNumber(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isOdd(3));
    }

    public function testIsOddReturnsFalseForEvenNumber(): void
    {
        $this->assertFalse(AlgorythmesGlobalHelpers::isOdd(4));
    }

    public function testIsOddReturnsFalseForZero(): void
    {
        $this->assertFalse(AlgorythmesGlobalHelpers::isOdd(0));
    }

    public function testIsOddReturnsTrueForNegativeOddNumber(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isOdd(-3));
    }

    public function testIsEvenReturnsTrueForEvenNumber(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isEven(4));
    }

    public function testIsEvenReturnsFalseForOddNumber(): void
    {
        $this->assertFalse(AlgorythmesGlobalHelpers::isEven(3));
    }

    public function testIsEvenReturnsTrueForZero(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isEven(0));
    }

    public function testIsEvenReturnsTrueForNegativeEvenNumber(): void
    {
        $this->assertTrue(AlgorythmesGlobalHelpers::isEven(-4));
    }

    // --- getMinAndMax ---

    public function testGetMinAndMaxFindsMinAndMaxRegardlessOfOrder(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([3, 1, 4, 1, 5, 9, 2, 6]);

        $this->assertSame(['min' => 1, 'max' => 9], $result);
    }

    public function testGetMinAndMaxWithSingleElementReturnsItAsBothMinAndMax(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([42]);

        $this->assertSame(['min' => 42, 'max' => 42], $result);
    }

    public function testGetMinAndMaxWithAllIdenticalValues(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([7, 7, 7]);

        $this->assertSame(['min' => 7, 'max' => 7], $result);
    }

    public function testGetMinAndMaxWithOnlyNegativeNumbers(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([-5, -1, -10, -3]);

        $this->assertSame(['min' => -10, 'max' => -1], $result);
    }

    public function testGetMinAndMaxWithMixOfNegativeAndPositiveNumbers(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([-5, 3, -1, 0, 8, -8, 2]);

        $this->assertSame(['min' => -8, 'max' => 8], $result);
    }

    public function testGetMinAndMaxWithFloats(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([3.3, 1.1, 4.4, 1.1, 5.5]);

        $this->assertSame(['min' => 1.1, 'max' => 5.5], $result);
    }

    public function testGetMinAndMaxWhenMinIsTheLastElement(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([5, 4, 3, 2, 1]);

        $this->assertSame(['min' => 1, 'max' => 5], $result);
    }

    public function testGetMinAndMaxWhenMaxIsTheLastElement(): void
    {
        $result = AlgorythmesGlobalHelpers::getMinAndMax([1, 2, 3, 4, 5]);

        $this->assertSame(['min' => 1, 'max' => 5], $result);
    }
}
