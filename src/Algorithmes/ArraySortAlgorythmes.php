<?php


namespace Zack\PhpDsAlgo\Algorithmes;

use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class ArraySortAlgorythmes
{
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
