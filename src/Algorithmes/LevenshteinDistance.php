<?php

declare(strict_types=1);

namespace Zack\PhpDsAlgo\Algorithmes;

use RuntimeException;

class LevenshteinDistance
{
    public static function calculate(string $word1, string $word2): array
    {
        $wordsData = self::getProcessedWordData($word1, $word2);

        $matrix = self::createDPTable($wordsData);
        $matrix = self::drawBorders($matrix, $wordsData);
        $matrix = self::buildTable($matrix, $wordsData);

        $path = self::buildingOptimalPathOfChanges($matrix, $wordsData);

        return [
            'WordsData' => $wordsData,
            'matrix' => $matrix,
            'path' => $path,
            'minimumEditDistance' => (int)$matrix[$wordsData['Xrows']][$wordsData['YColumns']],
        ];
    }

    private static function getProcessedWordData(string $word1, string $word2): array
    {
        $word1Spaced = ' ' . $word1;
        $word2Spaced = ' ' . $word2;

        return [
            'word1' => $word1,
            'word2' => $word2,
            'Word1Spaced' => $word1Spaced,
            'Word2Spaced' => $word2Spaced,
            'Xrows' => strlen($word1Spaced),
            'YColumns' => strlen($word2Spaced),
        ];
    }

    private static function createDPTable(array $wordsData): array
    {
        $matrix = array_fill(
            0,
            $wordsData['Xrows'] + 1,
            array_fill(0, $wordsData['YColumns'] + 1, '')
        );

        $matrix[0][0] = '-';

        for ($col = 1; $col <= $wordsData['YColumns']; $col++) {
            $matrix[0][$col] = $wordsData['Word2Spaced'][$col - 1];
        }

        for ($row = 1; $row <= $wordsData['Xrows']; $row++) {
            $matrix[$row][0] = $wordsData['Word1Spaced'][$row - 1];
        }

        return $matrix;
    }

    private static function drawBorders(array $matrix, array $wordsData): array
    {
        for ($i = 1; $i <= $wordsData['Xrows']; $i++) {
            $matrix[$i][1] = (string)($i - 1);
        }

        for ($i = 1; $i <= $wordsData['YColumns']; $i++) {
            $matrix[1][$i] = (string)($i - 1);
        }

        return $matrix;
    }

    private static function buildTable(array $matrix, array $wordsData): array
    {
        for ($j = 2; $j <= $wordsData['Xrows']; $j++) {
            for ($i = 2; $i <= $wordsData['YColumns']; $i++) {
                self::processCell($matrix, $i, $j);
            }
        }

        return $matrix;
    }

    private static function processCell(array &$matrix, int $i, int $j): void
    {
        $topRef = $matrix[0][$i];
        $leftRef = $matrix[$j][0];

        $topValue = (int)$matrix[$j - 1][$i];
        $leftValue = (int)$matrix[$j][$i - 1];
        $diagonalValue = (int)$matrix[$j - 1][$i - 1];

        $cost = ($topRef === $leftRef) ? 0 : 1;

        $matrix[$j][$i] = (string)min(
            $diagonalValue + $cost,
            $topValue + 1,
            $leftValue + 1
        );
    }

    private static function buildingOptimalPathOfChanges(array $matrix, array $wordsData): array
    {
        $steps = [];

        $i = $wordsData['YColumns'];
        $j = $wordsData['Xrows'];

        while ($i >= 2 || $j >= 2) {

            $neighbors = self::loadNeighbors($matrix, $i, $j);

            $step = self::processNeighbor(
                $neighbors,
                $steps,
                $matrix,
                $i,
                $j
            );

            switch ($step['direction']) {
                case 'Left':
                    $i--;
                    break;

                case 'Top':
                    $j--;
                    break;

                case 'Top-Left':
                    $i--;
                    $j--;
                    break;
            }
        }

        return $steps;
    }

    private static function processNeighbor(
        array $neighbors,
        array &$steps,
        array $matrix,
        int $i,
        int $j
    ): array {

        $topRef = $matrix[0][$i];
        $leftRef = $matrix[$j][0];

        $current = (int)$matrix[$j][$i];
        $top = (int)$neighbors['top'];
        $left = (int)$neighbors['left'];
        $diagonal = (int)$neighbors['diagonal'];

        $isMatch = ($topRef === $leftRef);

        if ($isMatch && $current === $diagonal) {

            $step = [
                'step' => 'Match',
                'from' => $leftRef,
                'to' => $topRef,
                'direction' => 'Top-Left',
            ];
        } elseif (!$isMatch && $current === $diagonal + 1) {

            $step = [
                'step' => 'Substitute',
                'from' => $leftRef,
                'to' => $topRef,
                'direction' => 'Top-Left',
            ];
        } elseif ($current === $left + 1) {

            $step = [
                'step' => 'Insert',
                'from' => '---',
                'to' => $topRef,
                'direction' => 'Left',
            ];
        } elseif ($current === $top + 1) {

            $step = [
                'step' => 'Delete',
                'from' => $leftRef,
                'to' => '---',
                'direction' => 'Top',
            ];
        } else {
            throw new RuntimeException('No valid predecessor found.');
        }

        $steps[] = $step;

        return $step;
    }

    private static function loadNeighbors(array $matrix, int $i, int $j): array
    {
        return [
            'top' => $matrix[$j - 1][$i],
            'left' => $matrix[$j][$i - 1],
            'diagonal' => $matrix[$j - 1][$i - 1],
        ];
    }
}
