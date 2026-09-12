<?php


namespace Zack\PhpDsAlgo\Algorithmes;

use InvalidArgumentException;
use Zack\PhpDsAlgo\DataStructure\Heap\MinHeap;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class ArraySortAlgorythmes
{
    /**
     * Sort a one-dimensional numeric array using the bucket sort algorithm.
     *
     * Accepts a flat array of `int|float` values — negative numbers, floats,
     * and duplicates are all handled: bucket placement is relative to the
     * array's own min/max, so it doesn't matter whether the range sits below
     * zero, straddles zero, or is entirely positive, and repeated values
     * always hash to the same bucket (via the identical arithmetic each
     * repetition of that value produces) so they stay adjacent in the
     * output.
     *
     * Every element is checked with `is_int()`/`is_float()` up front, before
     * any bucketing work happens: anything else — strings (numeric strings
     * included), bools, `null`, arrays, objects, resources — throws an
     * `InvalidArgumentException` rather than silently producing a wrong or
     * broken result. `NAN` and `INF`/`-INF` pass that check (they're still
     * floats) but remain unsupported in practice: `NAN` compares `false`
     * against everything, including itself, so the min/max scan in
     * {@see AlgorythmesGlobalHelpers::getMinAndMax()} and the per-bucket
     * `insertionSort()` can't place it correctly.
     *
     * Strategy:
     * 1. Fewer than 2 elements is already sorted — return as-is.
     * 2. Find the array's min and max (`getMinAndMax()`) and derive
     *    `$bucketCount = max(1, floor(sqrt(n)))` — the standard choice for
     *    uniformly distributed input, since it makes both the number of
     *    buckets and each bucket's expected population O(sqrt(n)).
     * 3. Divide the value range into `$bucketCount` equal-width slices
     *    (`$bucketWidth = $range / $bucketCount`). Every value's bucket
     *    index is `floor(($value - $min) / $bucketWidth)`, so bucket 0 holds
     *    the lowest slice of the range and the last bucket holds the
     *    highest; the value equal to `$max` would compute one index past
     *    the last bucket, so it's clamped back into it. When every value is
     *    identical (`$range === 0`), everything is placed in bucket 0
     *    instead of dividing by a zero-width bucket.
     * 4. Sort each non-empty bucket independently with insertionSort()
     *    (buckets are small on average, and a lone element needs no
     *    sorting).
     * 5. Concatenate the buckets in ascending index order — safe because
     *    every value in bucket *i* is `<=` every value in bucket *i+1* by
     *    construction, so the concatenation is already fully sorted. A
     *    highly uneven distribution (most values clustered in one bucket,
     *    others empty) doesn't break this — it only means that bucket's
     *    insertionSort() does more of the work.
     *
     * @param array<int, int|float> $data Values to sort; negatives, floats,
     *                                    and duplicates are all fine, but
     *                                    every element must be an `int` or
     *                                    `float` (see `@throws`).
     * @return array<int, int|float> A new array with the same values in
     *                                ascending order; the input array is not
     *                                mutated.
     *
     * @throws InvalidArgumentException if any element of $data is not an
     *                                   `int` or `float`.
     */
    public static function bucketSort(array $data): array
    {
        foreach ($data as $value) {
            if (!is_int($value) && !is_float($value)) {
                throw new InvalidArgumentException(
                    sprintf('Bucket sort only supports int and float values, got %s.', get_debug_type($value))
                );
            }
        }

        $n = count($data);
        // Guard: nothing to sort
        if ($n < 2) {
            return $data;
        }
        //Create buckets
        $minMaxData = AlgorythmesGlobalHelpers::getMinAndMax($data);
        $min = $minMaxData["min"];
        $max = $minMaxData["max"];
        $range = $max - $min;                    // float or int
        $bucketCount = max(1, (int) floor(sqrt($n)));
        $bucketWidth = ($range === 0) ? 0 : $range / $bucketCount;

        $buckets = array_fill(0, $bucketCount, []);
        //Put each value into its appropriate bucket
        foreach ($data as $value) {
            if ($range == 0) {
                $index = 0;                      // all values identical
            } else {
                $index = (int) floor(($value - $min) / $bucketWidth);
                if ($index >= $bucketCount) {
                    $index = $bucketCount - 1;   // clamp: v === max case
                }
            }

            $buckets[$index][] = $value;
        }

        //Sort each bucket
        $sorted = [];
        foreach ($buckets as $bucket) {
            if (empty($bucket)) {
                continue;
            }
            if (count($bucket) === 1) {
                $sorted[] = [$bucket[0]];
                continue;
            }
            $sorted[] = static::insertionSort($bucket);
        }

        //Merge all buckets
        //Sorted result

        return array_merge(...$sorted);
    }
    public static function heapSort(array $data): array
    {
        $sorted = [];
        $minHeap = new MinHeap($data);
        $size = $minHeap->size();
        for ($i = 0; $i < $size; $i++) {
            $sorted[] = $minHeap->extract();
        }
        return $sorted;
    }
    /* public static function selectionSort(array $nums): array
    {
        $length_nums = count($nums);
        for ($i = 0; $i <= $length_nums - 1; $i++) {
            $minimumIndex = $i;
            for ($j = $i + 1; $j <= $length_nums - 1; $j++) {
                if ($nums[$j] < $nums[$minimumIndex]) {
                    $minimumIndex = $j;
                }
            }
            if ($minimumIndex !== $i) {
                AlgorythmesGlobalHelpers::swapValuesOfArray($nums, $i, $minimumIndex);
            }
        }
        return $nums;
    } */
    public static function QuickSOrt(array $data): array
    {
        if (empty($data) || count($data) == 1) {
            return $data;
        }
        $low = 0;
        $height = count($data) - 1;
        self::prociessQuickSort($data, $low, $height);
        return $data;
    }
    private static function prociessQuickSort(array &$data, int $left, int $right)
    {
        if ($left >= $right) {
            return;
        }
        $pivot = self::partition($data, $left, $right);
        self::prociessQuickSort($data, $left, $pivot - 1);
        self::prociessQuickSort($data, $pivot + 1, $right);
    }

    private static function partition(array &$data, int $left, int $right): int
    {

        $pivot = $data[$left];
        $lt = $left + 1;
        $rt = $right;
        while ($lt <= $rt) {
            while ($lt <= $rt && $data[$lt] <= $pivot) {
                $lt++;
            }
            while ($rt >= $left && $data[$rt] > $pivot) {
                $rt--;
            }
            if ($lt < $rt) {
                AlgorythmesGlobalHelpers::swapValuesOfArray($data, $lt, $rt);
            }
        }
        AlgorythmesGlobalHelpers::swapValuesOfArray($data, $left, $rt);

        return $rt;
    }

    public static function MergeSort(array $data): array
    {
        if (empty($data) || count($data) == 1) {
            return $data;
        }
        $low = 0;
        $height = count($data) - 1;
        self::processMergeSort($data, $low, $height);
        return $data;
    }
    private static function processMergeSort(array &$data, int $low, int $height)
    {
        if ($low >= $height) {
            return;
        }
        $mid = (int) floor(($low + $height) / 2);
        self::processMergeSort($data, $low, $mid);
        self::processMergeSort($data, $mid + 1, $height);
        self::Merge($data, $low, $mid, $height);
    }
    private static function Merge(array &$data, int $low, int $mid, int $height)
    {
        $left = $low;
        $right = $mid + 1;
        $temp = [];

        while ($left <= $mid && $right <= $height) {

            if ($data[$left] <= $data[$right]) {
                $temp[] = $data[$left];
                $left++;
            } else {
                $temp[] = $data[$right];
                $right++;
            }
        }
        // copy leftovers from the left side
        while ($left <= $mid) {
            $temp[] = $data[$left];
            $left++;
        }
        // copy leftovers from the right side
        while ($right <= $height) {
            $temp[] = $data[$right];
            $right++;
        }
        // copy temp data to the original array
        for ($i = 0; $i < count($temp); $i++) {
            $data[$low + $i] = $temp[$i];
        }
    }
    public static function bubbleSort(array $nums): array
    {
        $length_nums = count($nums);
        for ($i = 1; $i < $length_nums; $i++) {
            for ($j = 0; $j < $length_nums - 1; $j++) {
                if ($nums[$j] > $nums[$j + 1]) {
                    AlgorythmesGlobalHelpers::swapValuesOfArray($nums, $j, $j + 1);
                }
            }
        }
        return $nums;
    }

    public static function selectionSort(array $nums): array
    {
        $length_nums = count($nums);
        for ($i = 0; $i <= $length_nums - 1; $i++) {
            $minimumIndex = $i;
            for ($j = $i + 1; $j <= $length_nums - 1; $j++) {
                if ($nums[$j] < $nums[$minimumIndex]) {
                    $minimumIndex = $j;
                }
            }
            if ($minimumIndex !== $i) {
                AlgorythmesGlobalHelpers::swapValuesOfArray($nums, $i, $minimumIndex);
            }
        }
        return $nums;
    }
    public static function insertionSort(array $nums): array
    {
        $length_nums = count($nums);
        for ($i = 1; $i <= $length_nums - 1; $i++) {
            $j = $i;
            while ($j > 0 && $nums[$j - 1] > $nums[$j]) {

                AlgorythmesGlobalHelpers::swapValuesOfArray($nums, $j, $j - 1);

                $j = $j - 1;
            }
        }
        return $nums;
    }
}
