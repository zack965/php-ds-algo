<?php


namespace Zack\PhpDsAlgo\Algorithmes;

use Zack\PhpDsAlgo\DataStructure\Heap\MinHeap;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

class ArraySortAlgorythmes
{
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
