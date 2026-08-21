<?php

namespace Zack\PhpDsAlgo\Algorithmes;

use InvalidArgumentException;

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
     * Determines whether an array contains a given value.
     *
     * Performs a linear scan (O(n)) and compares each element to $value using
     * strict comparison (===), so values of different types are never
     * considered equal (e.g. the int 1 and the string "1" are distinct).
     * Works with any value type — scalars, arrays, or objects (objects are
     * compared by identity, i.e. the same instance).
     *
     * @template T
     *
     * @param array<T> $data  The array to search. Order and keys are irrelevant.
     * @param T        $value The value to look for. Must not be null — pass a
     *                        sentinel or handle the "no value" case separately,
     *                        since null can never be an element of $data here.
     *
     * @return bool True if $data contains an element strictly equal to $value,
     *              false otherwise (including when $data is empty).
     *
     * @throws InvalidArgumentException If $value is null.
     */
    public static function contains(array $data, mixed $value): bool
    {
        if ($value === null) {
            throw new InvalidArgumentException('Value cannot be null.');
        }

        foreach ($data as $entry) {
            if ($entry === $value) {
                return true;
            }
        }

        return false;
    }
    /**
     * Returns a copy of an array with every element strictly equal to $value removed.
     *
     * This is a pure function: $data is never mutated. All occurrences of
     * $value are removed (not just the first), comparison is strict (===),
     * and the result is re-indexed into a contiguous list via array_values()
     * — original keys are not preserved. Callers must capture the return
     * value to observe the removal, e.g. `$data = self::remove($data, $x);`.
     *
     * @template T
     *
     * @param array<T> $data  The array to remove $value from. Left untouched.
     * @param T        $value The value to remove. Elements are matched with ===.
     *
     * @return list<T> A new array containing every element of $data that is
     *                  not strictly equal to $value, re-indexed from 0.
     */
    public static function remove(array $data, mixed $value): array
    {
        foreach ($data as $key => $entry) {
            if ($entry === $value) {
                unset($data[$key]);
            }
        }

        return array_values($data);
    }
}
