<?php


namespace Zack\PhpDsAlgo\Algorithmes;

use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class ArraySearchAlogorthme
{
    /**
     * Performs a recursive binary search on a sorted array.
     *
     * @param array<int> $nums Sorted array of integers
     * @param int $target Value to search for
     * @param int $end Right boundary index (inclusive)
     * @param int $start Left boundary index (default 0)
     * @return int Index of the target if found, otherwise -1
     */
    public static function binarySearch(array $nums, int $target, int $start, int $end = 0): int
    {
        if ($start > $end) {
            return -1;
        }
        $middle = $start + intdiv($end - $start, 2);

        if ($nums[$middle] == $target) {
            return $middle;
        } elseif ($nums[$middle] > $target) {
            return self::binarySearch($nums, $target, $start, $middle - 1);
        } else {
            return self::binarySearch($nums, $target, $middle + 1, $end);
        }
    }
    /**
     * Performs exponential search followed by binary search.
     *
     * @param array<int> $nums Sorted array of integers
     * @param int $target Value to search for
     * @return int Index of the target if found, otherwise -1
     */
    public static function exponentialSearchImplementation(array $nums, int $target): int
    {
        if ($nums == null || empty($nums)) {
            return -1; // Handle empty array
        }

        if ($nums[0] == $target) {
            return 0;
        }
        $i = 1;
        $numsLength = count($nums);
        while ($i < $numsLength && $nums[$i] <= $target) {
            $i = $i * 2;
        }

        return self::binarySearch($nums, $target, $i / 2, min($i, $numsLength - 1));
    }
    /**
     * Performs recursive interpolation search on a sorted array of integers.
     *
     * Interpolation search improves on binary search by estimating the position
     * of the target based on value distribution (useful for uniformly distributed data).
     *
     * @param array<int> $data Sorted array of integers (must be uniformly distributed for best performance)
     * @param int $target Value to search for
     * @param int $low Lower bound index
     * @param int $high Upper bound index
     * @return int Index of the target if found, otherwise -1
     */
    public static function interpolationSearchRecursive(array $data, int $target, int $low, int $high): int
    {
        if ($low <= $high && $target >= $data[$low] && $target <= $data[$high]) {
            // Estimate the position using the interpolation formula
            $pos = $low + (($target - $data[$low]) * ($high - $low)) / ($data[$high] - $data[$low]);

            // Check if the estimated position is the target
            if ($data[$pos] == $target) {
                return $pos;
            }

            // If target is larger, search in the upper part
            if ($data[$pos] < $target) {
                return self::interpolationSearchRecursive($data, $target, $pos + 1, $high);
            }

            // If target is smaller, search in the lower part
            if ($data[$pos] > $target) {
                return self::interpolationSearchRecursive($data, $target, $low, $pos - 1);
            }
        }

        // Element not found
        return -1;
    }
    /**
     * Performs Jump Search on a sorted array.
     *
     * Jump Search works by dividing the array into blocks of fixed size (jump size),
     * and skipping ahead until the target is likely within a block. Then a linear
     * search is performed inside that block.
     *
     * @param array $data Sorted array of integers to search in
     * @param int $target Value to search for
     * @param int $jumpSize Size of each jump/block
     * @param int $start Starting index of the current block
     * @param int $end Ending index of the current block
     * @param int $jumpIndex Current jump iteration counter
     *
     * @return int Index of the target if found, otherwise -1
     */
    public static function jumpSearch(array $data, int $target, int $jumpSize, int $start, int $end, int $jumpIndex)
    {
        $jumpIndex++;
        $data_count = count($data);

        if ($end >= $data_count) {
            $end = $data_count - 1;
        }

        if (AlgorythmesGlobalHelpers::isBetween($target, $data[$start], $data[$end])) {

            // Loop from start to end index
            for ($i = $start; $i <= $end; $i++) {
                if ($data[$i] == $target) {
                    return $i; // found
                }
            }
            return -1; // Not found in this block

        } else {
            // Move to the next block
            $newStart = $end + 1;
            $newEnd = $newStart + $jumpSize - 1;

            if ($newStart >= $data_count) {
                return -1; // Out of bounds, not found
            }

            return self::jumpSearch($data, $target, $jumpSize, $newStart, $newEnd, $jumpIndex);
        }
    }
    /**
     * Performs a linear search on an array to find the target value.
     *
     * This algorithm iterates through the array from the first element to the last,
     * comparing each value with the target. If a match is found, the index of the
     * matching element is returned. If the target does not exist in the array,
     * `-1` is returned.
     *
     * Time Complexity:
     * - Best Case: O(1)   - Target is the first element.
     * - Average Case: O(n) - Target is somewhere in the middle.
     * - Worst Case: O(n)  - Target is the last element or not present.
     *
     * Space Complexity:
     * - O(1)
     *
     * @param array<int|string, int|string> $data   The array to search (list or associative,
     *                                                with int or string values).
     * @param int|string                     $target The value to search for.
     *
     * @return int|string The index/key of the found element, or -1 if the array is empty
     *                     or the target is not found.
     */
    public static function linearSearch(array $data, int|string $target): int|string
    {
        if (empty($data)) {
            return -1;
        }

        foreach ($data as $index => $value) {
            if ($value === $target) {
                return $index;
            }
        }

        return -1;
    }
    /**
     * Performs a recursive ternary search on a sorted array.
     *
     * Ternary search splits the current range into three parts at `$mid1`
     * and `$mid2` instead of binary search's two, checking both midpoints
     * before recursing into whichever third the target must lie in.
     *
     * @param array<int, int|string> $data Sorted array to search in
     * @param int|string $target Value to search for
     * @return int Index of the target if found, otherwise -1
     */
    public static function TernarySearchAlgorythme(array $data, int|string $target): int
    {
        return self::TernarySearch($data, $target, 0, count($data) - 1);
    }
    /**
     * Recursive helper for {@see TernarySearchAlgorythme()}.
     *
     * @param array<int, int|string> $data Sorted array to search in
     * @param int|string $target Value to search for
     * @param int $low Left boundary index (inclusive)
     * @param int $high Right boundary index (inclusive)
     * @return int Index of the target if found, otherwise -1
     */
    private static function TernarySearch(array $data, int|string $target, int $low, int $high): int
    {
        if ($low > $high) {
            return -1;
        }
        $mid1 = $low + intdiv($high - $low, 3);
        $mid2 = $high -  intdiv($high - $low, 3);
        if ($data[$mid1] == $target) {
            return $mid1;
        }
        if ($data[$mid2] == $target) {
            return $mid2;
        }
        // left
        if ($data[$mid1] > $target) {
            return self::TernarySearch($data, $target, $low, $mid1 - 1);
        }
        // right
        if ($data[$mid2] < $target) {
            return self::TernarySearch($data, $target, $mid2 + 1, $high);
        }
        // in between
        if ($target > $data[$mid1] && $target < $data[$mid2]) {
            return self::TernarySearch($data, $target, $mid1 + 1, $mid2 - 1);
        }
        return -1;
    }
    public static function FibonacciSearchALgorythme(array $data, int $target): int
    {
        $count = count($data);
        $fabData = self::getClosestFibonacci($count);
        $f = $fabData["f1"];
        $f1 = $fabData["f2"];
        $f2 = $fabData["f3"];
        $offset = -1;

        while ($f > 1) {
            $probe = min($offset + $f2, $count - 1);
            if ($data[$probe] == $target) {
                return $probe;
            } elseif ($data[$probe] < $target) {
                $offset = $probe;
                $tempF1 = $f1;
                $tempF2 = $f2;

                $f  = $tempF1;
                $f1 = $tempF2;
                $f2 = $tempF1 - $tempF2;
            } elseif ($data[$probe] > $target) {
                $f  = $f2;
                $f1 = $f1 - $f2;
                $f2 = $f - $f1;
            }
        }
        if ($f1 == 1 && isset($data[$offset + 1]) && $data[$offset + 1] == $target) {
            return $offset + 1;
        }
        return -1;
    }



    public static function getClosestFibonacci(int $n): array
    {
        if ($n <= 1) {
            return [
                'f1' => 1,
                'f2' => 1,
                'f3' => 0,
            ];
        }

        $fibMm2 = 0; // (m-2)'th Fibonacci
        $fibMm1 = 1; // (m-1)'th Fibonacci
        $fibM   = $fibMm1 + $fibMm2;

        while ($fibM < $n) {
            $fibMm2 = $fibMm1;
            $fibMm1 = $fibM;
            $fibM = $fibMm1 + $fibMm2;
        }

        return [
            "f1" => $fibM,
            "f2" => $fibMm1,
            "f3" => $fibMm2
        ];
    }
}
