<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme;

class ArraySearchAlogorthmeTest extends TestCase
{
    // --- binarySearch ---
    //
    // binarySearch()'s signature is now ($nums, $target, $start, $end = 0),
    // matching what the recursive calls pass ($start, $middle - 1) /
    // ($middle + 1, $end) — start/end genuinely converge, so recursive
    // cases are safe to exercise (previously they weren't; see git history).

    public function testBinarySearchFindsValueAtComputedMiddleWithoutRecursion(): void
    {
        $nums = [10, 20, 30, 40, 50];

        $result = ArraySearchAlogorthme::binarySearch($nums, 30, 0, 4);

        $this->assertSame(2, $result);
    }

    public function testBinarySearchFindsFirstElementViaRecursion(): void
    {
        $nums = [10, 20, 30, 40, 50, 60, 70];

        $result = ArraySearchAlogorthme::binarySearch($nums, 10, 0, 6);

        $this->assertSame(0, $result);
    }

    public function testBinarySearchFindsLastElementViaRecursion(): void
    {
        $nums = [10, 20, 30, 40, 50, 60, 70];

        $result = ArraySearchAlogorthme::binarySearch($nums, 70, 0, 6);

        $this->assertSame(6, $result);
    }

    public function testBinarySearchReturnsNotFoundAfterRecursing(): void
    {
        $nums = [10, 20, 30, 40, 50, 60, 70];

        $result = ArraySearchAlogorthme::binarySearch($nums, 45, 0, 6);

        $this->assertSame(-1, $result);
    }

    public function testBinarySearchReturnsNotFoundWhenRangeIsImmediatelyInvalid(): void
    {
        $nums = [1, 2, 3];

        $result = ArraySearchAlogorthme::binarySearch($nums, 2, 2, 1);

        $this->assertSame(-1, $result);
    }

    // --- exponentialSearchImplementation ---

    public function testExponentialSearchFindsFirstElementWithoutRecursion(): void
    {
        $nums = [7, 20, 30, 40];

        $result = ArraySearchAlogorthme::exponentialSearchImplementation($nums, 7);

        $this->assertSame(0, $result);
    }

    public function testExponentialSearchOnEmptyArrayReturnsNotFound(): void
    {
        $result = ArraySearchAlogorthme::exponentialSearchImplementation([], 5);

        $this->assertSame(-1, $result);
    }

    public function testExponentialSearchFindsNonFirstElement(): void
    {
        $nums = range(1, 16);

        $result = ArraySearchAlogorthme::exponentialSearchImplementation($nums, 8);

        $this->assertSame(7, $result);
    }

    public function testExponentialSearchReturnsNotFoundWhenTargetAboveAllElements(): void
    {
        $nums = range(1, 16);

        $result = ArraySearchAlogorthme::exponentialSearchImplementation($nums, 100);

        $this->assertSame(-1, $result);
    }

    // --- interpolationSearchRecursive ---
    //
    // Unlike binarySearch(), this recurses with (pos + 1, high) / (low, pos - 1)
    // matching the (data, target, low, high) signature, so low/high genuinely
    // converge. Cases below are hand-verified to terminate.

    public function testInterpolationSearchFindsFirstElement(): void
    {
        $data = [10, 20, 30, 40, 50, 60, 70];

        $result = ArraySearchAlogorthme::interpolationSearchRecursive($data, 10, 0, 6);

        $this->assertSame(0, $result);
    }

    public function testInterpolationSearchFindsValueViaOvershootBranch(): void
    {
        // Concave growth (big early jumps, small later ones) makes the linear
        // estimate overshoot past the target on the first call, exercising
        // the `$data[$pos] > $target` / search-left branch instead of the
        // search-right branch the other cases above hit.
        $data = [1, 50, 60, 65, 68, 70];

        $result = ArraySearchAlogorthme::interpolationSearchRecursive($data, 65, 0, 5);

        $this->assertSame(3, $result);
    }

    public function testInterpolationSearchFindsMiddleElement(): void
    {
        $data = [10, 20, 30, 40, 50, 60, 70];

        $result = ArraySearchAlogorthme::interpolationSearchRecursive($data, 40, 0, 6);

        $this->assertSame(3, $result);
    }

    public function testInterpolationSearchFindsLastElement(): void
    {
        $data = [10, 20, 30, 40, 50, 60, 70];

        $result = ArraySearchAlogorthme::interpolationSearchRecursive($data, 70, 0, 6);

        $this->assertSame(6, $result);
    }

    public function testInterpolationSearchReturnsNotFoundWhenTargetOutsideRange(): void
    {
        $data = [1, 3, 5, 7, 9];

        $result = ArraySearchAlogorthme::interpolationSearchRecursive($data, 100, 0, 4);

        $this->assertSame(-1, $result);
    }

    public function testInterpolationSearchReturnsNotFoundAfterOneRecursion(): void
    {
        $data = [1, 3, 5, 7, 9];

        $result = ArraySearchAlogorthme::interpolationSearchRecursive($data, 4, 0, 4);

        $this->assertSame(-1, $result);
    }

    // --- jumpSearch ---

    public function testJumpSearchFindsValueInFirstBlock(): void
    {
        $data = [1, 3, 5, 7, 9, 11, 13, 15];

        $result = ArraySearchAlogorthme::jumpSearch($data, 5, 3, 0, 2, 0);

        $this->assertSame(2, $result);
    }

    public function testJumpSearchReturnsNotFoundWhenValueIsMissingWithinAMatchedBlock(): void
    {
        // target(5) falls within the value range of the first block ([1, 2, 10]),
        // so isBetween() is true, but 5 isn't actually one of the block's
        // values — exercises the "scanned the block, no match" return, as
        // opposed to the "wrong block entirely" case the other not-found
        // test below covers.
        $data = [1, 2, 10, 20, 21, 22];

        $result = ArraySearchAlogorthme::jumpSearch($data, 5, 3, 0, 2, 0);

        $this->assertSame(-1, $result);
    }

    public function testJumpSearchFindsValueAfterOneJump(): void
    {
        $data = [1, 3, 5, 7, 9, 11, 13, 15];

        $result = ArraySearchAlogorthme::jumpSearch($data, 11, 3, 0, 2, 0);

        $this->assertSame(5, $result);
    }

    public function testJumpSearchFindsValueAfterTwoJumpsWithClampedBlock(): void
    {
        $data = [1, 3, 5, 7, 9, 11, 13, 15];

        $result = ArraySearchAlogorthme::jumpSearch($data, 13, 3, 0, 2, 0);

        $this->assertSame(6, $result);
    }

    public function testJumpSearchReturnsNotFoundWhenValueAboveAllBlocks(): void
    {
        $data = [1, 3, 5, 7, 9, 11, 13, 15];

        $result = ArraySearchAlogorthme::jumpSearch($data, 100, 3, 0, 2, 0);

        $this->assertSame(-1, $result);
    }
}
