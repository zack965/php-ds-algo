<?php

namespace Zack\PhpDsAlgo\Algorithmes\Strings;

/**
 * Knuth-Morris-Pratt (KMP) string searching algorithm.
 *
 * Provides methods for building the Longest Suffix-Prefix (LSP) table
 * and searching for all occurrences of a pattern within a text.
 */
class KMP
{
    /**
     * Calculates the Longest Suffix-Prefix (LSP) table for a pattern.
     *
     * Each value represents the length of the longest proper prefix
     * of the pattern ending at the current position that is also a suffix.
     *
     * The LSP table is used by the KMP algorithm to avoid unnecessary
     * comparisons after a mismatch.
     *
     * @param array<int, string> $data Pattern represented as an array of characters.
     *
     * @return array<int, int> LSP table indexed by the character position.
     */
    public static function calculateLspTable(array $data): array
    {

        $lspTable = array_fill(0, count($data), 0);
        $prefixLength = 0;
        $i = 1;
        while ($i < count($data)) {
            $element = $data[$i];
            $prefixElement = $data[$prefixLength];

            if ($element == $prefixElement) {
                $prefixLength++;
                $lspTable[$i] = $prefixLength;
            } else {
                if ($prefixLength != 0) {
                    $prefixLength = $lspTable[$prefixLength - 1];
                    continue;
                } else {
                    $lspTable[$i] = 0;
                }
            }

            $i++;
        }
        return $lspTable;
    }
    /**
     * Finds all occurrences of a pattern within a text using the KMP algorithm.
     *
     * Returns the zero-based starting indexes of every occurrence of the pattern.
     * Overlapping occurrences are included.
     *
     * An empty text or empty pattern returns an empty array.
     *
     * @param string $text Text to search within.
     * @param string $pattern Pattern to search for.
     *
     * @return array<int, int> Zero-based starting indexes of all pattern occurrences.
     */
    public static function run(string $text, string $pattern): array
    {
        // Handle empty pattern
        if ($pattern === '') {
            return [];
        }

        $str_array = str_split($text);
        $pattenr_array = str_split($pattern);
        $pattenr_array_count = count($pattenr_array);
        if (empty($str_array)) {
            return [];
        }

        $indexes = [];
        $lspTable = self::calculateLspTable($pattenr_array);
        $i = 0; // loop over the string
        $j = 0; // loop over LPS table
        while ($i < count($str_array)) {
            $element = $str_array[$i];
            $lspElement = $pattenr_array[$j];
            if ($element == $lspElement) {
                $j++;
                $i++;
                if ($j == $pattenr_array_count) {
                    $indexes[] = $i - $j;
                    $j = $lspTable[$j - 1];
                }
            } else {
                if ($j != 0) {
                    $j = $lspTable[$j - 1];
                } else {
                    $i++;
                }
            }
        }

        return $indexes;
    }
}
