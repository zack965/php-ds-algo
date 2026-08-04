<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\LevenshteinDistance;

class LevenshteinDistanceTest extends TestCase
{
    // --- minimumEditDistance ---

    public function testCalculateReturnsZeroForIdenticalWords(): void
    {
        $result = LevenshteinDistance::calculate('cat', 'cat');

        $this->assertSame(0, $result['minimumEditDistance']);
    }

    public function testCalculateReturnsZeroForTwoEmptyStrings(): void
    {
        $result = LevenshteinDistance::calculate('', '');

        $this->assertSame(0, $result['minimumEditDistance']);
    }

    public function testCalculateReturnsLengthOfWord2WhenWord1IsEmpty(): void
    {
        $result = LevenshteinDistance::calculate('', 'abc');

        $this->assertSame(3, $result['minimumEditDistance']);
    }

    public function testCalculateReturnsLengthOfWord1WhenWord2IsEmpty(): void
    {
        $result = LevenshteinDistance::calculate('abc', '');

        $this->assertSame(3, $result['minimumEditDistance']);
    }

    public function testCalculateSingleCharacterSubstitution(): void
    {
        $result = LevenshteinDistance::calculate('a', 'b');

        $this->assertSame(1, $result['minimumEditDistance']);
    }

    public function testCalculateSingleWholeWordSubstitution(): void
    {
        $result = LevenshteinDistance::calculate('cat', 'bat');

        $this->assertSame(1, $result['minimumEditDistance']);
    }

    public function testCalculateWellKnownKittenToSitting(): void
    {
        $result = LevenshteinDistance::calculate('kitten', 'sitting');

        $this->assertSame(3, $result['minimumEditDistance']);
    }

    public function testCalculateWellKnownHorseToRos(): void
    {
        $result = LevenshteinDistance::calculate('horse', 'ros');

        $this->assertSame(3, $result['minimumEditDistance']);
    }

    public function testCalculateWellKnownIntentionToExecution(): void
    {
        $result = LevenshteinDistance::calculate('intention', 'execution');

        $this->assertSame(5, $result['minimumEditDistance']);
    }

    public function testCalculateIsCaseSensitive(): void
    {
        // 'C' and 'c' are compared with ===, so this costs one substitution
        // rather than being treated as a match.
        $result = LevenshteinDistance::calculate('Cat', 'cat');

        $this->assertSame(1, $result['minimumEditDistance']);
    }

    // --- WordsData ---

    public function testCalculateWordsDataReflectsInputAndSpacedLengths(): void
    {
        $result = LevenshteinDistance::calculate('cat', 'bat');

        $this->assertSame([
            'word1' => 'cat',
            'word2' => 'bat',
            'Word1Spaced' => ' cat',
            'Word2Spaced' => ' bat',
            'Xrows' => 4,
            'YColumns' => 4,
        ], $result['WordsData']);
    }

    // --- matrix dimensions ---

    public function testCalculateMatrixHasXrowsPlusOneRowsAndYColumnsPlusOneColumns(): void
    {
        $result = LevenshteinDistance::calculate('cat', 'bat');

        $this->assertCount(5, $result['matrix']);
        $this->assertCount(5, $result['matrix'][0]);
    }

    // --- optimal path of changes ---
    //
    // path() walks the matrix back from the bottom-right corner to the
    // top-left, so steps come out in reverse order (last character first).
    // Each case below was hand-traced against the DP recurrence to pin the
    // exact step sequence produced.

    public function testCalculatePathForIdenticalSingleCharacterIsOneMatch(): void
    {
        $result = LevenshteinDistance::calculate('a', 'a');

        $this->assertSame([
            ['step' => 'Match', 'from' => 'a', 'to' => 'a', 'direction' => 'Top-Left'],
        ], $result['path']);
    }

    public function testCalculatePathForSubstitutionEndsWithSubstituteStep(): void
    {
        $result = LevenshteinDistance::calculate('cat', 'bat');

        $this->assertSame([
            ['step' => 'Match', 'from' => 't', 'to' => 't', 'direction' => 'Top-Left'],
            ['step' => 'Match', 'from' => 'a', 'to' => 'a', 'direction' => 'Top-Left'],
            ['step' => 'Substitute', 'from' => 'c', 'to' => 'b', 'direction' => 'Top-Left'],
        ], $result['path']);
    }

    public function testCalculatePathForDeletionIncludesDeleteStep(): void
    {
        // "ab" -> "a" drops the trailing 'b'.
        $result = LevenshteinDistance::calculate('ab', 'a');

        $this->assertSame([
            ['step' => 'Delete', 'from' => 'b', 'to' => '---', 'direction' => 'Top'],
            ['step' => 'Match', 'from' => 'a', 'to' => 'a', 'direction' => 'Top-Left'],
        ], $result['path']);
    }

    public function testCalculatePathForInsertionIncludesInsertStep(): void
    {
        // "a" -> "ab" adds a trailing 'b'.
        $result = LevenshteinDistance::calculate('a', 'ab');

        $this->assertSame([
            ['step' => 'Insert', 'from' => '---', 'to' => 'b', 'direction' => 'Left'],
            ['step' => 'Match', 'from' => 'a', 'to' => 'a', 'direction' => 'Top-Left'],
        ], $result['path']);
    }
}
