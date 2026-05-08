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
    public static function binarySearch(array $nums, int $target, int $end, int $start = 0): int
    {
        if ($start > $end) {
            return -1;
        }
        $middle = $start + ($end - $start) / 2;

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
    public static function jumpSearch(array $data, int $target, int $jumpSize, int $start, int $end,int $jumpIndex) {
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

}
