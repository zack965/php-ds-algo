<?php

namespace Tests\Unit\Algorithmes;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    // --- heapSort ---

    public function testHeapSortSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::heapSort([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testHeapSortHandlesAlreadySortedArray(): void
    {
        $result = ArraySortAlgorythmes::heapSort([1, 2, 3, 4, 5]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testHeapSortHandlesReverseSortedArray(): void
    {
        $result = ArraySortAlgorythmes::heapSort([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testHeapSortHandlesDuplicateValues(): void
    {
        $result = ArraySortAlgorythmes::heapSort([3, 1, 2, 1, 3]);

        $this->assertSame([1, 1, 2, 3, 3], $result);
    }

    public function testHeapSortHandlesEmptyArray(): void
    {
        $this->assertSame([], ArraySortAlgorythmes::heapSort([]));
    }

    public function testHeapSortHandlesSingleElementArray(): void
    {
        $this->assertSame([42], ArraySortAlgorythmes::heapSort([42]));
    }

    public function testHeapSortHandlesTwoElementArray(): void
    {
        $this->assertSame([1, 2], ArraySortAlgorythmes::heapSort([2, 1]));
    }

    public function testHeapSortHandlesNegativeNumbers(): void
    {
        $result = ArraySortAlgorythmes::heapSort([0, -3, 5, -1, 2]);

        $this->assertSame([-3, -1, 0, 2, 5], $result);
    }

    public function testHeapSortDoesNotMutateInputArray(): void
    {
        $input = [3, 1, 2];

        ArraySortAlgorythmes::heapSort($input);

        $this->assertSame([3, 1, 2], $input);
    }

    // --- bucketSort ---

    // n < 2 guard

    public function testBucketSortHandlesEmptyArray(): void
    {
        $this->assertSame([], ArraySortAlgorythmes::bucketSort([]));
    }

    public function testBucketSortHandlesSingleElementArray(): void
    {
        $this->assertSame([42], ArraySortAlgorythmes::bucketSort([42]));
    }

    // basic ordering

    public function testBucketSortSortsUnorderedArray(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([5, 3, 1, 4, 2]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testBucketSortHandlesAlreadySortedArray(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([1, 2, 3, 4, 5]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testBucketSortHandlesReverseSortedArray(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testBucketSortHandlesTwoElementArray(): void
    {
        $this->assertSame([1, 2], ArraySortAlgorythmes::bucketSort([2, 1]));
    }

    // two elements is the n < 4 case where bucketCount collapses to 1
    // (floor(sqrt(2)) === 1), so everything lands in a single bucket.

    public function testBucketSortHandlesTwoElementArrayAlreadySorted(): void
    {
        $this->assertSame([1, 2], ArraySortAlgorythmes::bucketSort([1, 2]));
    }

    public function testBucketSortHandlesThreeElementArray(): void
    {
        // floor(sqrt(3)) === 1 too, so this also exercises the single-bucket path.
        $this->assertSame([1, 2, 3], ArraySortAlgorythmes::bucketSort([3, 1, 2]));
    }

    public function testBucketSortHandlesFourElementArraySpanningTwoBuckets(): void
    {
        // floor(sqrt(4)) === 2: the first array where more than one bucket
        // is actually used, so this exercises the multi-bucket merge path.
        $this->assertSame([1, 2, 3, 4], ArraySortAlgorythmes::bucketSort([4, 1, 3, 2]));
    }

    // duplicates

    public function testBucketSortHandlesDuplicateValues(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([3, 1, 2, 1, 3]);

        $this->assertSame([1, 1, 2, 3, 3], $result);
    }

    public function testBucketSortHandlesAllIdenticalValues(): void
    {
        // min === max, so $range is 0 and every value must be routed into
        // bucket 0 directly instead of dividing by a zero-width bucket.
        $result = ArraySortAlgorythmes::bucketSort([5, 5, 5, 5, 5]);

        $this->assertSame([5, 5, 5, 5, 5], $result);
    }

    public function testBucketSortHandlesAllIdenticalFloatValues(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([2.5, 2.5, 2.5]);

        $this->assertSame([2.5, 2.5, 2.5], $result);
    }

    public function testBucketSortHandlesManyDuplicatesClusteredWithOutliers(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([2, 2, 2, 2, 1, 2, 2, 3, 2, 2]);

        $this->assertSame([1, 2, 2, 2, 2, 2, 2, 2, 2, 3], $result);
    }

    // negative numbers

    public function testBucketSortHandlesOnlyNegativeNumbers(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([-5, -1, -10, -3, -7]);

        $this->assertSame([-10, -7, -5, -3, -1], $result);
    }

    public function testBucketSortHandlesMixOfNegativeAndPositiveNumbers(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([-5, 3, -1, 0, 8, -8, 2]);

        $this->assertSame([-8, -5, -1, 0, 2, 3, 8], $result);
    }

    public function testBucketSortHandlesNegativeNumbersStraddlingZeroWithDuplicates(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([-2, 0, 2, -2, 0, 2]);

        $this->assertSame([-2, -2, 0, 0, 2, 2], $result);
    }

    // floats

    public function testBucketSortSortsFloats(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([3.3, 1.1, 4.4, 1.1, 5.5, 9.9, 2.2, 6.6]);

        $this->assertSame([1.1, 1.1, 2.2, 3.3, 4.4, 5.5, 6.6, 9.9], $result);
    }

    public function testBucketSortSortsNegativeFloats(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([-1.5, -1.5, 0.0, 2.25, -3.75, 2.25]);

        $this->assertSame([-3.75, -1.5, -1.5, 0.0, 2.25, 2.25], $result);
    }

    public function testBucketSortSortsFloatsThatAreVeryCloseTogether(): void
    {
        // All five values fall within a span of ~0.0000012, so this leans
        // hard on the per-bucket insertionSort() rather than on the bucket
        // split itself doing any of the ordering work.
        $result = ArraySortAlgorythmes::bucketSort([0.1, 0.1000001, 0.10000001, 0.1, 0.09999999]);

        $this->assertSame([0.09999999, 0.1, 0.1, 0.10000001, 0.1000001], $result);
    }

    public function testBucketSortSortsMixOfIntAndFloatValues(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([3, 1.5, 2, 0.5, 4]);

        $this->assertSame([0.5, 1.5, 2, 3, 4], $result);
    }

    // highly uneven distributions

    public function testBucketSortHandlesHighlyUnevenDistributionWithOneOutlier(): void
    {
        // Nine consecutive small values plus one huge outlier: with
        // bucketCount === floor(sqrt(10)) === 3, all nine small values
        // collapse into a single bucket and one bucket is left empty, so
        // this exercises both "one bucket does almost all the work" and
        // "an empty bucket must be skipped when merging".
        $result = ArraySortAlgorythmes::bucketSort([1, 2, 3, 4, 5, 6, 7, 8, 9, 1_000_000]);

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 1_000_000], $result);
    }

    public function testBucketSortHandlesHighlyUnevenDistributionWithOutlierAtTheStart(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([1_000_000, 1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 1_000_000], $result);
    }

    public function testBucketSortHandlesHighlyUnevenDistributionSpanningNegativeAndPositive(): void
    {
        $result = ArraySortAlgorythmes::bucketSort([-1_000_000, -5, -4, -3, -2, -1, 0, 1, 2, 1_000_000]);

        $this->assertSame([-1_000_000, -5, -4, -3, -2, -1, 0, 1, 2, 1_000_000], $result);
    }

    public function testBucketSortHandlesTwoDistantOutlierClusters(): void
    {
        // Two tight clusters far apart, with nothing in between: most
        // middle buckets stay empty regardless of bucket count.
        $result = ArraySortAlgorythmes::bucketSort([1, 2, 3, 1000, 1001, 1002]);

        $this->assertSame([1, 2, 3, 1000, 1001, 1002], $result);
    }

    // does not mutate input

    public function testBucketSortDoesNotMutateInputArray(): void
    {
        $input = [3, 1, 2];

        ArraySortAlgorythmes::bucketSort($input);

        $this->assertSame([3, 1, 2], $input);
    }

    // --- bucketSort: element type validation ---

    public function testBucketSortAcceptsOnlyIntAndFloatValues(): void
    {
        // Sanity check that the validation added below doesn't reject the
        // types it's meant to allow.
        $result = ArraySortAlgorythmes::bucketSort([3, 1.5, -2, 0.0]);

        $this->assertSame([-2, 0.0, 1.5, 3], $result);
    }

    #[DataProvider('nonIntOrFloatValueProvider')]
    public function testBucketSortRejectsNonIntOrFloatValues(mixed $invalidValue): void
    {
        $this->expectException(InvalidArgumentException::class);

        ArraySortAlgorythmes::bucketSort([1, 2, $invalidValue, 3]);
    }

    public static function nonIntOrFloatValueProvider(): array
    {
        return [
            'string' => ['not-a-number'],
            'numeric string' => ['42'],
            'bool true' => [true],
            'bool false' => [false],
            'null' => [null],
            'array' => [[1, 2]],
            'object' => [new \stdClass()],
        ];
    }

    public function testBucketSortRejectionMessageNamesTheOffendingType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket sort only supports int and float values, got string.');

        ArraySortAlgorythmes::bucketSort([1, 2, 'oops']);
    }

    public function testBucketSortRejectsInvalidValueAtTheStart(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ArraySortAlgorythmes::bucketSort(['not-a-number', 1, 2, 3]);
    }

    public function testBucketSortRejectsSingleInvalidElementEvenBelowTheSortThreshold(): void
    {
        // A one-element array normally short-circuits via the n < 2 guard
        // before any bucketing happens, but type validation runs before
        // that guard, so it still must throw here.
        $this->expectException(InvalidArgumentException::class);

        ArraySortAlgorythmes::bucketSort(['not-a-number']);
    }
}
