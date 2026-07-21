<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;

class GeneralArrayAlgorithmsTest extends TestCase
{
    // --- hasDuplicates ---

    public function testHasDuplicatesReturnsTrueWhenDuplicatesExist(): void
    {
        $this->assertTrue(GeneralArrayAlgorithms::hasDuplicates([1, 2, 3, 2]));
    }

    public function testHasDuplicatesReturnsFalseWhenAllValuesUnique(): void
    {
        $this->assertFalse(GeneralArrayAlgorithms::hasDuplicates([1, 2, 3, 4]));
    }

    public function testHasDuplicatesReturnsFalseForEmptyArray(): void
    {
        $this->assertFalse(GeneralArrayAlgorithms::hasDuplicates([]));
    }

    public function testHasDuplicatesReturnsFalseForSingleElementArray(): void
    {
        $this->assertFalse(GeneralArrayAlgorithms::hasDuplicates([1]));
    }

    public function testHasDuplicatesDetectsStringDuplicates(): void
    {
        $this->assertTrue(GeneralArrayAlgorithms::hasDuplicates(['a', 'b', 'a']));
    }

    // --- contains ---

    public function testContainsReturnsTrueWhenValuePresent(): void
    {
        $this->assertTrue(GeneralArrayAlgorithms::contains([1, 2, 3], 2));
    }

    public function testContainsReturnsFalseWhenValueAbsent(): void
    {
        $this->assertFalse(GeneralArrayAlgorithms::contains([1, 2, 3], 99));
    }

    public function testContainsReturnsFalseForEmptyArray(): void
    {
        $this->assertFalse(GeneralArrayAlgorithms::contains([], 1));
    }

    public function testContainsUsesStrictComparison(): void
    {
        $this->assertFalse(GeneralArrayAlgorithms::contains(['1', 2, 3], 1));
    }

    public function testContainsWorksWithStringValues(): void
    {
        $this->assertTrue(GeneralArrayAlgorithms::contains(['a', 'b', 'c'], 'b'));
    }
}
