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

    // --- linearSearch ---

    public function testLinearSearchFindsFirstElement(): void
    {
        $data = [10, 20, 30, 40, 50];

        $result = ArraySearchAlogorthme::linearSearch($data, 10);

        $this->assertSame(0, $result);
    }

    public function testLinearSearchFindsMiddleElement(): void
    {
        $data = [10, 20, 30, 40, 50];

        $result = ArraySearchAlogorthme::linearSearch($data, 30);

        $this->assertSame(2, $result);
    }

    public function testLinearSearchFindsLastElement(): void
    {
        $data = [10, 20, 30, 40, 50];

        $result = ArraySearchAlogorthme::linearSearch($data, 50);

        $this->assertSame(4, $result);
    }

    public function testLinearSearchReturnsNotFoundWhenTargetIsMissing(): void
    {
        $data = [10, 20, 30, 40, 50];

        $result = ArraySearchAlogorthme::linearSearch($data, 100);

        $this->assertSame(-1, $result);
    }

    public function testLinearSearchOnEmptyArrayReturnsNotFound(): void
    {
        $result = ArraySearchAlogorthme::linearSearch([], 5);

        $this->assertSame(-1, $result);
    }

    public function testLinearSearchWorksOnStringValues(): void
    {
        $data = ['apple', 'banana', 'cherry'];

        $result = ArraySearchAlogorthme::linearSearch($data, 'banana');

        $this->assertSame(1, $result);
    }

    public function testLinearSearchUsesStrictComparisonAndDoesNotMatchTypeJuggledValues(): void
    {
        // "0" and 0 are `==` equal in PHP but not `===` equal; linearSearch()
        // uses `===`, so a string needle must not match an int haystack value.
        $data = [0, 1, 2];

        $result = ArraySearchAlogorthme::linearSearch($data, '0');

        $this->assertSame(-1, $result);
    }

    public function testLinearSearchReturnsStringKeyForAssociativeArray(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $result = ArraySearchAlogorthme::linearSearch($data, 2);

        $this->assertSame('b', $result);
    }

    public function testLinearSearchReturnsFirstMatchWhenValueAppearsMultipleTimes(): void
    {
        $data = [5, 10, 10, 10, 20];

        $result = ArraySearchAlogorthme::linearSearch($data, 10);

        $this->assertSame(1, $result);
    }

    // --- TernarySearchAlgorythme ---
    //
    // TernarySearchAlgorythme() is the public wrapper (data, target) around
    // the private, genuinely-converging TernarySearch(data, target, low, high)
    // recursion, so cases below drive it purely through the wrapper.

    public function testTernarySearchFindsFirstElement(): void
    {
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 1);

        $this->assertSame(0, $result);
    }

    public function testTernarySearchFindsLastElement(): void
    {
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 20);

        $this->assertSame(19, $result);
    }

    public function testTernarySearchFindsValueAtFirstMidpoint(): void
    {
        // For range(1, 20) (low=0, high=19), mid1 = 0 + intdiv(19, 3) = 6,
        // so target 7 (value at index 6) is matched by the `$data[$mid1] ==
        // $target` branch on the very first call, without recursing.
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 7);

        $this->assertSame(6, $result);
    }

    public function testTernarySearchFindsValueAtSecondMidpoint(): void
    {
        // mid2 = 19 - intdiv(19, 3) = 13, so target 14 (value at index 13)
        // is matched by the `$data[$mid2] == $target` branch on the first call.
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 14);

        $this->assertSame(13, $result);
    }

    public function testTernarySearchFindsValueInLeftThirdViaRecursion(): void
    {
        // Target 3 is below mid1's value (7), exercising the
        // "$data[$mid1] > $target" / recurse-left branch.
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 3);

        $this->assertSame(2, $result);
    }

    public function testTernarySearchFindsValueInRightThirdViaRecursion(): void
    {
        // Target 18 is above mid2's value (14), exercising the
        // "$data[$mid2] < $target" / recurse-right branch.
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 18);

        $this->assertSame(17, $result);
    }

    public function testTernarySearchFindsValueInMiddleThirdViaRecursion(): void
    {
        // Target 10 sits strictly between mid1's value (7) and mid2's
        // value (14), exercising the "in between" recursion branch.
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 10);

        $this->assertSame(9, $result);
    }

    public function testTernarySearchReturnsNotFoundWhenTargetIsMissing(): void
    {
        $data = range(1, 20);

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 100);

        $this->assertSame(-1, $result);
    }

    public function testTernarySearchOnEmptyArrayReturnsNotFound(): void
    {
        $result = ArraySearchAlogorthme::TernarySearchAlgorythme([], 5);

        $this->assertSame(-1, $result);
    }

    public function testTernarySearchOnSingleElementArrayFindsIt(): void
    {
        $result = ArraySearchAlogorthme::TernarySearchAlgorythme([42], 42);

        $this->assertSame(0, $result);
    }

    public function testTernarySearchOnSingleElementArrayReturnsNotFoundWhenMissing(): void
    {
        $result = ArraySearchAlogorthme::TernarySearchAlgorythme([42], 1);

        $this->assertSame(-1, $result);
    }

    public function testTernarySearchWorksOnStringValues(): void
    {
        $data = ['apple', 'banana', 'cherry', 'date', 'fig', 'grape'];

        $result = ArraySearchAlogorthme::TernarySearchAlgorythme($data, 'banana');

        $this->assertSame(1, $result);
    }
}
