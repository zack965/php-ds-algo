<?php

namespace Tests\Unit\DataStructure\Set;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Set\Set;
use stdClass;

class SetTest extends TestCase
{
    // --- Construction ---

    public function testConstructWithNoArgumentsIsEmpty(): void
    {
        $set = new Set();

        $this->assertTrue($set->isEmpty());
        $this->assertSame(0, $set->count());
        $this->assertSame([], $set->getAll());
    }

    public function testConstructFromListKeepsUniqueValues(): void
    {
        $set = new Set([1, 2, 3]);

        $this->assertSame(3, $set->count());
        $this->assertSame([1, 2, 3], $set->getAll());
    }

    public function testConstructFromListWithDuplicatesDeduplicates(): void
    {
        $set = new Set([1, 1, 2, 2, 2, 3]);

        $this->assertSame(3, $set->count());
        $this->assertSame([1, 2, 3], $set->getAll());
    }

    public function testConstructFromListKeepsFirstOccurrenceOrder(): void
    {
        $set = new Set(['b', 'a', 'b', 'c', 'a']);

        $this->assertSame(['b', 'a', 'c'], $set->getAll());
    }

    // --- add() ---

    public function testAddNewValueReturnsTrueAndInsertsIt(): void
    {
        $set = new Set();

        $this->assertTrue($set->add(1));
        $this->assertTrue($set->contains(1));
        $this->assertSame(1, $set->count());
    }

    public function testAddExistingValueReturnsFalseAndLeavesSetUnchanged(): void
    {
        $set = new Set([1]);

        $this->assertFalse($set->add(1));
        $this->assertSame(1, $set->count());
        $this->assertSame([1], $set->getAll());
    }

    public function testAddDistinguishesValuesByStrictType(): void
    {
        $set = new Set([1]);

        $this->assertTrue($set->add('1'));
        $this->assertSame(2, $set->count());
        $this->assertSame([1, '1'], $set->getAll());
    }

    public function testAddDistinguishesDistinctObjectInstances(): void
    {
        $set = new Set();
        $a = new stdClass();
        $b = new stdClass();

        $this->assertTrue($set->add($a));
        $this->assertTrue($set->add($b));
        $this->assertSame(2, $set->count());
    }

    public function testAddSameObjectInstanceTwiceIsRejectedSecondTime(): void
    {
        $set = new Set();
        $object = new stdClass();

        $this->assertTrue($set->add($object));
        $this->assertFalse($set->add($object));
        $this->assertSame(1, $set->count());
    }

    public function testAddNullThrows(): void
    {
        $set = new Set();

        $this->expectException(InvalidArgumentException::class);
        $set->add(null);
    }

    // --- contains() ---

    public function testContainsReturnsTrueForPresentValue(): void
    {
        $set = new Set([1, 2, 3]);

        $this->assertTrue($set->contains(2));
    }

    public function testContainsReturnsFalseForAbsentValue(): void
    {
        $set = new Set([1, 2, 3]);

        $this->assertFalse($set->contains(99));
    }

    public function testContainsReturnsFalseForEmptySet(): void
    {
        $set = new Set();

        $this->assertFalse($set->contains(1));
    }

    public function testContainsUsesStrictComparison(): void
    {
        $set = new Set(['1', 2, 3]);

        $this->assertFalse($set->contains(1));
        $this->assertTrue($set->contains('1'));
    }

    // --- remove() ---

    public function testRemoveExistingValueReturnsTrueAndDeletesIt(): void
    {
        $set = new Set([1, 2, 3]);

        $this->assertTrue($set->remove(2));
        $this->assertFalse($set->contains(2));
        $this->assertSame(2, $set->count());
        $this->assertSame([1, 3], $set->getAll());
    }

    public function testRemoveAbsentValueReturnsFalseAndLeavesSetUnchanged(): void
    {
        $set = new Set([1, 2, 3]);

        $this->assertFalse($set->remove(99));
        $this->assertSame(3, $set->count());
        $this->assertSame([1, 2, 3], $set->getAll());
    }

    public function testRemoveFromEmptySetReturnsFalse(): void
    {
        $set = new Set();

        $this->assertFalse($set->remove(1));
    }

    public function testRemovedValueCanBeAddedAgain(): void
    {
        $set = new Set([1]);
        $set->remove(1);

        $this->assertTrue($set->add(1));
        $this->assertSame([1], $set->getAll());
    }

    // --- clear() ---

    public function testClearEmptiesTheSet(): void
    {
        $set = new Set([1, 2, 3]);
        $set->clear();

        $this->assertTrue($set->isEmpty());
        $this->assertSame(0, $set->count());
        $this->assertSame([], $set->getAll());
    }

    public function testClearOnEmptySetIsANoop(): void
    {
        $set = new Set();
        $set->clear();

        $this->assertTrue($set->isEmpty());
    }

    // --- isEmpty() ---

    public function testIsEmptyIsTrueForNewSet(): void
    {
        $this->assertTrue((new Set())->isEmpty());
    }

    public function testIsEmptyIsFalseAfterAdd(): void
    {
        $set = new Set();
        $set->add(1);

        $this->assertFalse($set->isEmpty());
    }

    // --- getAll() ---

    public function testGetAllReturnsSnapshotNotLiveReference(): void
    {
        $set = new Set([1, 2]);
        $values = $set->getAll();
        $values[] = 3;

        $this->assertSame([1, 2], $set->getAll());
    }

    // --- count() ---

    public function testCountReflectsAddsAndRemoves(): void
    {
        $set = new Set();

        $this->assertSame(0, $set->count());

        $set->add(1);
        $set->add(2);
        $this->assertSame(2, $set->count());

        $set->remove(1);
        $this->assertSame(1, $set->count());
    }

    // --- IteratorAggregate ---

    public function testForeachIteratesAllValuesInInsertionOrder(): void
    {
        $set = new Set([3, 1, 2]);

        $seen = [];
        foreach ($set as $value) {
            $seen[] = $value;
        }

        $this->assertSame([3, 1, 2], $seen);
    }

    // --- union() ---

    public function testUnionCombinesValuesFromBothSets(): void
    {
        $a = new Set([1, 2]);
        $b = new Set([2, 3]);

        $union = $a->union($b);

        $this->assertSame([1, 2, 3], $union->getAll());
    }

    public function testUnionReturnsNewInstanceAndDoesNotMutateOperands(): void
    {
        $a = new Set([1, 2]);
        $b = new Set([2, 3]);

        $union = $a->union($b);

        $this->assertNotSame($a, $union);
        $this->assertSame([1, 2], $a->getAll());
        $this->assertSame([2, 3], $b->getAll());
    }

    public function testUnionWithEmptySetReturnsEquivalentSet(): void
    {
        $a = new Set([1, 2]);
        $b = new Set();

        $this->assertSame([1, 2], $a->union($b)->getAll());
        $this->assertSame([1, 2], $b->union($a)->getAll());
    }

    // --- intersection() ---

    public function testIntersectionReturnsOnlyCommonValues(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([2, 3, 4]);

        $this->assertSame([2, 3], $a->intersection($b)->getAll());
    }

    public function testIntersectionOfDisjointSetsIsEmpty(): void
    {
        $a = new Set([1, 2]);
        $b = new Set([3, 4]);

        $this->assertTrue($a->intersection($b)->isEmpty());
    }

    public function testIntersectionDoesNotMutateOperands(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([2, 3, 4]);

        $a->intersection($b);

        $this->assertSame([1, 2, 3], $a->getAll());
        $this->assertSame([2, 3, 4], $b->getAll());
    }

    // --- difference() ---

    public function testDifferenceReturnsValuesOnlyInThisSet(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([2, 3, 4]);

        $this->assertSame([1], $a->difference($b)->getAll());
    }

    public function testDifferenceIsDirectional(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([2, 3, 4]);

        $this->assertSame([1], $a->difference($b)->getAll());
        $this->assertSame([4], $b->difference($a)->getAll());
    }

    public function testDifferenceWithEmptySetReturnsAllValues(): void
    {
        $a = new Set([1, 2]);
        $b = new Set();

        $this->assertSame([1, 2], $a->difference($b)->getAll());
    }

    public function testDifferenceOfSetWithItselfIsEmpty(): void
    {
        $a = new Set([1, 2]);

        $this->assertTrue($a->difference($a)->isEmpty());
    }

    // --- isSubsetOf() ---

    public function testIsSubsetOfReturnsTrueWhenAllValuesArePresentInOther(): void
    {
        $a = new Set([1, 2]);
        $b = new Set([1, 2, 3]);

        $this->assertTrue($a->isSubsetOf($b));
    }

    public function testIsSubsetOfReturnsFalseWhenAValueIsMissing(): void
    {
        $a = new Set([1, 2, 4]);
        $b = new Set([1, 2, 3]);

        $this->assertFalse($a->isSubsetOf($b));
    }

    public function testEmptySetIsSubsetOfAnySet(): void
    {
        $empty = new Set();
        $other = new Set([1, 2]);

        $this->assertTrue($empty->isSubsetOf($other));
        $this->assertTrue($empty->isSubsetOf(new Set()));
    }

    public function testSetIsSubsetOfItself(): void
    {
        $a = new Set([1, 2]);

        $this->assertTrue($a->isSubsetOf($a));
    }

    // --- isSupersetOf() ---

    public function testIsSupersetOfReturnsTrueWhenContainingAllOfOther(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([1, 2]);

        $this->assertTrue($a->isSupersetOf($b));
    }

    public function testIsSupersetOfReturnsFalseWhenMissingAValue(): void
    {
        $a = new Set([1, 2]);
        $b = new Set([1, 2, 3]);

        $this->assertFalse($a->isSupersetOf($b));
    }

    public function testAnySetIsSupersetOfEmptySet(): void
    {
        $a = new Set([1, 2]);

        $this->assertTrue($a->isSupersetOf(new Set()));
    }

    // --- equals() ---

    public function testEqualsReturnsTrueForSameValuesInSameOrder(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([1, 2, 3]);

        $this->assertTrue($a->equals($b));
    }

    public function testEqualsReturnsTrueForSameValuesInDifferentOrder(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([3, 1, 2]);

        $this->assertTrue($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentSizes(): void
    {
        $a = new Set([1, 2]);
        $b = new Set([1, 2, 3]);

        $this->assertFalse($a->equals($b));
    }

    public function testEqualsReturnsFalseForSameSizeDifferentValues(): void
    {
        $a = new Set([1, 2, 3]);
        $b = new Set([1, 2, 4]);

        $this->assertFalse($a->equals($b));
    }

    public function testEqualsIsTrueForTwoEmptySets(): void
    {
        $this->assertTrue((new Set())->equals(new Set()));
    }

    public function testSetEqualsItself(): void
    {
        $a = new Set([1, 2, 3]);

        $this->assertTrue($a->equals($a));
    }
}
