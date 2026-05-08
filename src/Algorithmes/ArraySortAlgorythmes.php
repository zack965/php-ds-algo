<?php


namespace Zack\PhpDsAlgo\Algorithmes;

class ArraySortAlgorythmes
{
    public static function bubbleSort(array $nums):array
    {
        $length_nums = count($nums);
        for ($i = 1; $i < $length_nums ; $i++) {
            for ($j = 0; $j < $length_nums -1 ; $j++) {
                if($nums[$j] > $nums[$j+1]){
                    self::swapValuesOfArray($nums,$j,$j+1);
                }
            }
        }
        return $nums;
    }
    public static function swapValuesOfArray(array &$nums , int $startIndex,int $endIndex):void {
        $temp = $nums[$startIndex];
        $nums[$startIndex] = $nums[$endIndex];
        $nums[$endIndex] = $temp;
    }
}
