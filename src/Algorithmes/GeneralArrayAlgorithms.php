<?php

namespace Zack\PhpDsAlgo\Algorithmes;

final class GeneralArrayAlgorithms
{
    /**
     * Determines whether the array contains duplicate values.
     *
     * @param array<int, int|string> $data
     */
    public static function hasDuplicates(array $data): bool
    {
        $seen = [];

        foreach ($data as $value) {
            if (isset($seen[$value])) {
                return true;
            }

            $seen[$value] = true;
        }

        return false;
    }
    /**
     * Determines whether the array contains the given value.
     *
     * @param array<int, int|string> $data
     * @param int|string $value
     */
    public static function contains(array $data, int|string $value): bool
    {
        foreach ($data as $entry) {
            if ($entry === $value) {
                return true;
            }
        }

        return false;
    }
}
