<?php


namespace Zack\PhpDsAlgo;

use InvalidArgumentException;

class SortingAlgorithms
{
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
                self::swapValuesOfArray($nums, $i, $minimumIndex);
            }
        }
        return $nums;
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