<?php


namespace Zack\PhpDsAlgo\Helpers\Algorythmes;

use InvalidArgumentException;

/**
 * Small, shared primitives used across `src/Algorithmes/` — kept here
 * instead of duplicated inline in each algorithm class.
 */
class AlgorythmesGlobalHelpers
{
    /**
     * Determines whether $number falls within [$lower, $upper], inclusive
     * on both ends.
     */
    public static function isBetween(int $number, int $lower, int $upper): bool
    {
        return $number >= $lower && $number <= $upper;
    }
    /**
     * Swaps the values at $startIndex and $endIndex in $nums, in place.
     *
     * @throws InvalidArgumentException if either index does not exist in $nums
     */
    public static function swapValuesOfArray(array &$nums, int $startIndex, int $endIndex): void
    {

        if (!array_key_exists($startIndex, $nums)) {
            throw new InvalidArgumentException("Start index {$startIndex} does not exist in the array.");
        }

        if (!array_key_exists($endIndex, $nums)) {
            throw new InvalidArgumentException("End index {$endIndex} does not exist in the array.");
        }
        $temp = $nums[$startIndex];
        $nums[$startIndex] = $nums[$endIndex];
        $nums[$endIndex] = $temp;
    }
    /**
     * Determines whether $value is odd.
     */
    public static function isOdd(int $value): bool
    {
        return $value % 2 !== 0;
    }

    /**
     * Determines whether $value is even.
     */
    public static function isEven(int $value): bool
    {
        return $value % 2 === 0;
    }

    /**
     * Get the minimum and maximum values from an array.
     *
     * Single linear pass, so it works for arrays in any order (not just
     * sorted ones). An empty $data triggers a PHP warning ("Undefined array
     * key 0") when the initial `$data[0]` read fails — this is not
     * validated against, so callers must not pass an empty array.
     *
     * @param array<int, int|float> $data Non-empty; int/float only.
     * @return array{min: int|float, max: int|float}
     */
    public static function getMinAndMax(array $data): array
    {
        $min = $data[0];
        $max = $data[0];
        foreach ($data as $element) {
            if ($min > $element) {
                $min = $element;
            }
            if ($max < $element) {
                $max = $element;
            }
        }
        return [
            "min" => $min,
            "max" => $max
        ];
    }
}
