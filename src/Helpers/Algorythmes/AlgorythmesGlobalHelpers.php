<?php


namespace Zack\PhpDsAlgo\Helpers\Algorythmes;

use InvalidArgumentException;

class AlgorythmesGlobalHelpers
{
    public static function isBetween(int $number, int $lower, int $upper): bool
    {
        return $number >= $lower && $number <= $upper;
    }
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
}
