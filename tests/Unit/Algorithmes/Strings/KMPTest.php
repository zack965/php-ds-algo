<?php

namespace Tests\Unit\Algorithmes\Strings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\Strings\KMP;

class KMPTest extends TestCase
{
    #[DataProvider('lspTableProvider')]
    public function testCalculateLspTable(
        array $data,
        array $expected
    ): void {
        $this->assertSame(
            $expected,
            KMP::calculateLspTable($data)
        );
    }
    public static function lspTableProvider(): array
    {
        return [
            'empty array' => [
                [],
                [],
            ],

            'single character' => [
                ['A'],
                [0],
            ],

            'two different characters' => [
                ['A', 'B'],
                [0, 0],
            ],

            'two equal characters' => [
                ['A', 'A'],
                [0, 1],
            ],

            // ...
        ];
    }

    #[DataProvider('runProvider')]
    public function testRun(
        string $text,
        string $pattern,
        array $expected
    ): void {
        $this->assertSame(
            $expected,
            KMP::run($text, $pattern)
        );
    }


    public static function runProvider(): array
    {
        return [
            'empty pattern' => [
                'ABC',
                '',
                [],
            ],

            'empty text' => [
                '',
                'ABC',
                [],
            ],

            'both empty' => [
                '',
                '',
                [],
            ],

            'pattern not found' => [
                'ABCDEF',
                'XYZ',
                [],
            ],

            'pattern equals text' => [
                'ABC',
                'ABC',
                [0],
            ],

            'pattern at beginning' => [
                'ABCDEF',
                'ABC',
                [0],
            ],

            'pattern at end' => [
                'ABCDEF',
                'DEF',
                [3],
            ],

            'pattern in the middle' => [
                'XABCY',
                'ABC',
                [1],
            ],

            'multiple matches' => [
                'ABCABCABC',
                'ABC',
                [0, 3, 6],
            ],

            'overlapping matches' => [
                'ABABA',
                'ABA',
                [0, 2],
            ],

            'multiple overlapping matches' => [
                'AAAAA',
                'AAA',
                [0, 1, 2],
            ],

            'single character match' => [
                'ABC',
                'B',
                [1],
            ],

            'single character no match' => [
                'ABC',
                'X',
                [],
            ],

            'single character text match' => [
                'A',
                'A',
                [0],
            ],

            'single character text no match' => [
                'A',
                'B',
                [],
            ],

            'pattern longer than text' => [
                'AB',
                'ABC',
                [],
            ],

            'repeated characters' => [
                'AAAAAA',
                'AA',
                [0, 1, 2, 3, 4],
            ],

            'classic KMP example' => [
                'ABABDABACDABABCABAB',
                'ABABCABAB',
                [10],
            ],

            'multiple occurrences with fallback' => [
                'AABAACAADAABAABA',
                'AABA',
                [0, 9, 12],
            ],
        ];
    }
}
