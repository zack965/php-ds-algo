<?php


namespace Zack\PhpDsAlgo\Helpers\Algorythmes;
class AlgorythmesGlobalHelpers
{
    public static function isBetween(int $number, int $lower, int $upper): bool
    {
        return $number >= $lower && $number <= $upper;
    }
}