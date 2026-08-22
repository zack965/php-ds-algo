<?php

namespace Tests\Unit\DataStructure\HashTabe;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Contracts\IHashSet;
use Zack\PhpDsAlgo\DataStructure\HashTabe\HashSet;

class HashSetTest extends TestCase
{
    /**
     * Mirrors HashSet's private tableHash() so tests can assert exactly
     * which bucket a value is expected to land in.
     */
    private function expectedIndex(string $value, int $capacity): int
    {
        $hash = hash('xxh3', $value);
        $hashInteger = hexdec(substr($hash, 0, 8));

        return $hashInteger % $capacity;
    }

    // --- Construction ---

    public function testConstructWithDefaultCapacity(): void
    {
        $set = new HashSet();

        $this->assertSame(10, $set->getCapacity());
        $this->assertTrue($set->isEmpty());
    }

    public function testConstructWithCustomCapacity(): void
    {
        $set = new HashSet(5);

        $this->assertSame(5, $set->getCapacity());
    }

    public function testConstructThrowsWhenCapacityIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be greater than 0.');

        new HashSet(0);
    }

    public function testConstructThrowsWhenCapacityIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be greater than 0.');

        new HashSet(-3);
    }

    public function testImplementsIHashSet(): void
    {
        $this->assertInstanceOf(IHashSet::class, new HashSet());
    }

    // --- insert / hasValue ---

    public function testInsertMakesValueFindable(): void
    {
        $set = new HashSet();

        $set->insert('hello');

        $this->assertTrue($set->hasValue('hello'));
    }

    public function testHasValueReturnsFalseForMissingValue(): void
    {
        $set = new HashSet();

        $this->assertFalse($set->hasValue('missing'));
    }

    public function testInsertPlacesValueInExpectedBucket(): void
    {
        $set = new HashSet(10);
        $value = 'bucket-target';

        $set->insert($value);

        $expected = $this->expectedIndex($value, 10);
        $this->assertSame(
            ['bucketIndex' => $expected, 'itemIndex' => 0],
            $set->getValuePosition($value)
        );
    }

    public function testInsertHandlesCollisionsViaChaining(): void
    {
        // 'item-2', 'item-5' and 'item-48' all hash to bucket 25 on a
        // capacity-100 table, exercising separate chaining without ever
        // approaching the 0.7 load factor that would trigger a resize.
        $set = new HashSet(100);

        $set->insert('item-2');
        $set->insert('item-5');
        $set->insert('item-48');

        $this->assertTrue($set->hasValue('item-2'));
        $this->assertTrue($set->hasValue('item-5'));
        $this->assertTrue($set->hasValue('item-48'));
        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 0],
            $set->getValuePosition('item-2')
        );
        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 1],
            $set->getValuePosition('item-5')
        );
        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 2],
            $set->getValuePosition('item-48')
        );
    }

    public function testInsertIgnoresDuplicateValues(): void
    {
        // Unlike HashTable (a bag - duplicates allowed), HashSet is a real
        // set: each bucket is a Set, which already de-duplicates via add().
        $set = new HashSet();

        $set->insert('dup');
        $set->insert('dup');

        $this->assertSame(1, $set->getSize());
    }

    public function testInsertTriggersResizeWhenLoadFactorExceedsThreshold(): void
    {
        // Capacity 10: the 8th insert pushes load factor to 0.8 (> 0.7),
        // triggering a resize that doubles the capacity to 20.
        $set = new HashSet(10);

        for ($i = 0; $i < 8; $i++) {
            $set->insert("value-{$i}");
        }

        $this->assertSame(20, $set->getCapacity());
        $this->assertSame(8, $set->getSize());
    }

    public function testInsertDoesNotResizeBeforeThresholdIsCrossed(): void
    {
        // 7 inserts on capacity 10 gives a load factor of exactly 0.7,
        // which is not strictly greater than 0.7.
        $set = new HashSet(10);

        for ($i = 0; $i < 7; $i++) {
            $set->insert("value-{$i}");
        }

        $this->assertSame(10, $set->getCapacity());
    }

    public function testAllValuesRemainFindableAfterResize(): void
    {
        $set = new HashSet(10);

        $values = [];
        for ($i = 0; $i < 8; $i++) {
            $values[] = "value-{$i}";
            $set->insert("value-{$i}");
        }

        foreach ($values as $value) {
            $this->assertTrue($set->hasValue($value));
        }
    }

    // --- delete ---

    public function testDeleteRemovesExistingValue(): void
    {
        $set = new HashSet();
        $set->insert('gone');

        $result = $set->delete('gone');

        $this->assertTrue($result);
        $this->assertFalse($set->hasValue('gone'));
    }

    public function testDeleteReturnsFalseForMissingValue(): void
    {
        $set = new HashSet();

        $this->assertFalse($set->delete('missing'));
    }

    public function testDeleteReducesSize(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->insert('b');

        $set->delete('a');

        $this->assertSame(1, $set->getSize());
    }

    public function testDeleteFromEmptySetReturnsFalse(): void
    {
        $set = new HashSet();

        $this->assertFalse($set->delete('anything'));
    }

    // --- getValuePosition ---

    public function testGetValuePositionReturnsBucketAndItemIndex(): void
    {
        $set = new HashSet(10);
        $value = 'positioned';

        $set->insert($value);

        $expectedBucket = $this->expectedIndex($value, 10);
        $this->assertSame(
            ['bucketIndex' => $expectedBucket, 'itemIndex' => 0],
            $set->getValuePosition($value)
        );
    }

    public function testGetValuePositionReturnsFalseForMissingValue(): void
    {
        $set = new HashSet();

        $this->assertFalse($set->getValuePosition('missing'));
    }

    public function testGetValuePositionReflectsItemIndexWithinBucket(): void
    {
        // 'item-2' and 'item-5' collide into bucket 25 on a capacity-100 table.
        $set = new HashSet(100);
        $set->insert('item-2');
        $set->insert('item-5');

        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 1],
            $set->getValuePosition('item-5')
        );
    }

    // --- update ---

    public function testUpdateReplacesExistingValue(): void
    {
        $set = new HashSet();
        $set->insert('old');

        $result = $set->update('old', 'new');

        $this->assertTrue($result);
        $this->assertFalse($set->hasValue('old'));
        $this->assertTrue($set->hasValue('new'));
    }

    public function testUpdateReturnsFalseWhenOldValueMissing(): void
    {
        $set = new HashSet();

        $this->assertFalse($set->update('missing', 'new'));
    }

    public function testUpdateKeepsSizeUnchanged(): void
    {
        $set = new HashSet();
        $set->insert('old');
        $set->insert('other');

        $set->update('old', 'new');

        $this->assertSame(2, $set->getSize());
    }

    public function testUpdateMovesValueToNewValuesBucket(): void
    {
        $set = new HashSet(10);
        $set->insert('old');

        $set->update('old', 'new');

        $expected = $this->expectedIndex('new', 10);
        $this->assertSame(
            ['bucketIndex' => $expected, 'itemIndex' => 0],
            $set->getValuePosition('new')
        );
    }

    public function testUpdateReturnsFalseWhenNewValueAlreadyExists(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->insert('b');

        $result = $set->update('a', 'b');

        $this->assertFalse($result);
        $this->assertTrue($set->hasValue('a'));
        $this->assertTrue($set->hasValue('b'));
        $this->assertSame(2, $set->getSize());
    }

    public function testUpdateIsNoopSuccessWhenOldAndNewValuesAreTheSame(): void
    {
        $set = new HashSet();
        $set->insert('a');

        $this->assertTrue($set->update('a', 'a'));
        $this->assertSame(1, $set->getSize());
    }

    public function testUpdateReturnsFalseInsteadOfThrowingForInvalidOldValue(): void
    {
        $set = new HashSet();
        $set->insert('a');

        $this->assertFalse($set->update(null, 'b'));
        $this->assertFalse($set->update([1, 2], 'b'));
    }

    public function testUpdateReturnsFalseInsteadOfThrowingForInvalidNewValue(): void
    {
        $set = new HashSet();
        $set->insert('a');

        $this->assertFalse($set->update('a', null));
        $this->assertFalse($set->update('a', [1, 2]));
        // Neither call mutated the set, since both are rejected up front.
        $this->assertTrue($set->hasValue('a'));
    }

    public function testUpdateOfNanWithItselfSucceedsAsNoop(): void
    {
        // Regression test: the no-op short-circuit must use the same
        // NAN-aware equality as the rest of the class (===  is always false
        // for NAN === NAN), or this would incorrectly report failure even
        // though nothing needs to change.
        $set = new HashSet();
        $set->insert(NAN);

        $this->assertTrue($set->update(NAN, NAN));
        $this->assertSame(1, $set->getSize());
        $this->assertTrue($set->hasValue(NAN));
    }

    // --- getAllValues ---

    public function testGetAllValuesReturnsEmptyArrayForEmptySet(): void
    {
        $set = new HashSet();

        $this->assertSame([], $set->getAllValues());
    }

    public function testGetAllValuesReturnsEveryInsertedValue(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->insert('b');
        $set->insert('c');

        $this->assertEqualsCanonicalizing(['a', 'b', 'c'], $set->getAllValues());
    }

    public function testGetAllValuesExcludesDeletedValues(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->insert('b');
        $set->delete('a');

        $this->assertSame(['b'], $set->getAllValues());
    }

    // --- getSize / getCapacity / getLoadFactor ---

    public function testGetSizeReturnsZeroForEmptySet(): void
    {
        $this->assertSame(0, (new HashSet())->getSize());
    }

    public function testGetSizeCountsAcrossAllBuckets(): void
    {
        $set = new HashSet(1);
        $set->insert('a');
        $set->insert('b');
        $set->insert('c');

        $this->assertSame(3, $set->getSize());
    }

    public function testGetCapacityReturnsConfiguredCapacity(): void
    {
        $this->assertSame(7, (new HashSet(7))->getCapacity());
    }

    public function testGetLoadFactorReflectsSizeOverCapacity(): void
    {
        $set = new HashSet(4);
        $set->insert('a');
        $set->insert('b');

        $this->assertSame(0.5, $set->getLoadFactor());
    }

    public function testGetLoadFactorIsZeroForEmptySet(): void
    {
        $this->assertSame(0.0, (new HashSet())->getLoadFactor());
    }

    // --- resize ---

    public function testResizeDoublesCapacity(): void
    {
        $set = new HashSet(4);

        $set->resize();

        $this->assertSame(8, $set->getCapacity());
    }

    public function testResizePreservesAllValues(): void
    {
        $set = new HashSet(4);
        $set->insert('a');
        $set->insert('b');
        $set->insert('c');

        $set->resize();

        $this->assertEqualsCanonicalizing(['a', 'b', 'c'], $set->getAllValues());
        $this->assertSame(3, $set->getSize());
    }

    public function testResizeRebucketsValuesByNewCapacity(): void
    {
        $set = new HashSet(4);
        $value = 'rebucketed';
        $set->insert($value);

        $set->resize();

        $expected = $this->expectedIndex($value, 8);
        $this->assertSame(
            ['bucketIndex' => $expected, 'itemIndex' => 0],
            $set->getValuePosition($value)
        );
    }

    // --- clear ---

    public function testClearEmptiesTheSet(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->insert('b');

        $set->clear();

        $this->assertTrue($set->isEmpty());
        $this->assertSame(0, $set->getSize());
        $this->assertSame([], $set->getAllValues());
    }

    public function testClearPreservesCapacity(): void
    {
        $set = new HashSet(20);
        $set->insert('a');

        $set->clear();

        $this->assertSame(20, $set->getCapacity());
    }

    // --- reset ---

    public function testResetRestoresDefaultCapacity(): void
    {
        $set = new HashSet(50);
        $set->insert('a');

        $set->reset();

        $this->assertSame(10, $set->getCapacity());
    }

    public function testResetEmptiesTheSet(): void
    {
        $set = new HashSet(50);
        $set->insert('a');
        $set->insert('b');

        $set->reset();

        $this->assertTrue($set->isEmpty());
        $this->assertSame(0, $set->getSize());
    }

    // --- isEmpty ---

    public function testIsEmptyReturnsTrueForNewSet(): void
    {
        $this->assertTrue((new HashSet())->isEmpty());
    }

    public function testIsEmptyReturnsFalseAfterInsert(): void
    {
        $set = new HashSet();
        $set->insert('a');

        $this->assertFalse($set->isEmpty());
    }

    public function testIsEmptyReturnsTrueAfterDeletingOnlyValue(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->delete('a');

        $this->assertTrue($set->isEmpty());
    }

    // --- Generic (non-string) values ---

    public function testInsertAndHasValueWorkWithIntegers(): void
    {
        $set = new HashSet();

        $set->insert(42);

        $this->assertTrue($set->hasValue(42));
        $this->assertFalse($set->hasValue(43));
    }

    public function testInsertAndHasValueWorkWithFloats(): void
    {
        $set = new HashSet();

        $set->insert(3.14);

        $this->assertTrue($set->hasValue(3.14));
    }

    public function testInsertAndHasValueWorkWithBooleans(): void
    {
        $set = new HashSet();

        $set->insert(true);
        $set->insert(false);

        $this->assertTrue($set->hasValue(true));
        $this->assertTrue($set->hasValue(false));
    }

    public function testHasValueDistinguishesValuesByTypeNotJustLooseEquality(): void
    {
        // hasValue() compares with ===, so the int 1, the string "1" and
        // true must all be tracked as distinct entries even if two of them
        // land in the same bucket.
        $set = new HashSet();
        $set->insert(1);

        $this->assertTrue($set->hasValue(1));
        $this->assertFalse($set->hasValue('1'));
        $this->assertFalse($set->hasValue(true));
    }

    public function testInsertAndHasValueWorkWithObjects(): void
    {
        // hasValue() compares with ===, which for objects is identity, not
        // value equality - a clone with identical properties is a
        // different instance and must not be reported as present.
        $set = new HashSet();
        $value = new \DateTimeImmutable('2024-01-01');

        $set->insert($value);

        $this->assertTrue($set->hasValue($value));
        $this->assertFalse($set->hasValue(clone $value));
    }

    public function testHasValueUsesIdentityForObjectsWithoutValueEquality(): void
    {
        // Two distinct stdClass instances with the same property may or may
        // not share a bucket (bucketing is by spl_object_id here, not by
        // serialized value), but === must still distinguish them by identity
        // either way.
        $set = new HashSet();
        $a = new \stdClass();
        $a->name = 'same';
        $b = new \stdClass();
        $b->name = 'same';

        $set->insert($a);

        $this->assertTrue($set->hasValue($a));
        $this->assertFalse($set->hasValue($b));
    }

    public function testInsertThrowsForClosures(): void
    {
        // Closures are objects, but have no stable value representation and
        // are explicitly rejected - same as HashTable/HashMap.
        $set = new HashSet();

        $this->expectException(InvalidArgumentException::class);

        $set->insert(fn () => 1);
    }

    public function testInsertThrowsForResources(): void
    {
        $set = new HashSet();
        $resource = fopen('php://memory', 'r');

        try {
            $this->expectException(InvalidArgumentException::class);

            $set->insert($resource);
        } finally {
            fclose($resource);
        }
    }

    public function testInsertThrowsForNull(): void
    {
        $set = new HashSet();

        $this->expectException(InvalidArgumentException::class);

        $set->insert(null);
    }

    public function testInsertThrowsForArrays(): void
    {
        // Outside HashSet's `@template T of scalar|object` bound, unlike
        // HashTable, which does accept arrays.
        $set = new HashSet();

        $this->expectException(InvalidArgumentException::class);

        $set->insert(['id' => 1]);
    }

    public function testGetAllValuesPreservesMixedTypes(): void
    {
        $set = new HashSet();
        $set->insert(1);
        $set->insert('1');
        $set->insert(1.0);

        $this->assertEqualsCanonicalizing([1, '1', 1.0], $set->getAllValues());
    }

    public function testHasValueTreatsNegativeZeroAsEqualToPositiveZero(): void
    {
        // Regression test: `-0.0 === 0.0` is true in PHP (the comparison
        // this class uses for equality), but serialize(-0.0) and
        // serialize(0.0) produce different strings ("d:-0;" vs "d:0;").
        // Without normalizing -0.0 before hashing, the two values would
        // land in different buckets and hasValue(0.0) would incorrectly
        // report a stored -0.0 as absent.
        $set = new HashSet();
        $set->insert(-0.0);

        $this->assertTrue($set->hasValue(0.0));
        $this->assertTrue($set->hasValue(-0.0));
    }

    public function testDeleteFindsValueInsertedAsNegativeZero(): void
    {
        $set = new HashSet();
        $set->insert(-0.0);

        $this->assertTrue($set->delete(0.0));
        $this->assertTrue($set->isEmpty());
    }

    public function testInsertNegativeZeroAndPositiveZeroAreTheSameSetElement(): void
    {
        // Unlike HashTable (a bag), HashSet de-duplicates: -0.0 and 0.0
        // compare === equal, so inserting both only ever yields one entry.
        $set = new HashSet();

        $set->insert(-0.0);
        $set->insert(0.0);

        $this->assertSame(1, $set->getSize());
    }

    public function testInsertingNanTwiceOnlyStoresOneEntry(): void
    {
        // Regression test: NAN === NAN is always false in PHP, so a naive
        // strict-comparison dedup would never recognize a second NAN as a
        // duplicate. The shared GeneralArrayAlgorithms::equals() carve-out
        // treats two NAN floats as equal to each other specifically to
        // keep this invariant intact.
        $set = new HashSet();

        $set->insert(NAN);
        $set->insert(NAN);

        $this->assertSame(1, $set->getSize());
    }

    public function testDeleteRemovesNanEntry(): void
    {
        // Regression test: delete() must use the same NAN-aware equality as
        // hasValue()/contains(), or a NAN element could be reported present
        // yet never actually removed.
        $set = new HashSet();
        $set->insert(NAN);

        $this->assertTrue($set->delete(NAN));
        $this->assertSame(0, $set->getSize());
        $this->assertFalse($set->hasValue(NAN));
    }

    // --- IteratorAggregate / Countable ---

    public function testForeachYieldsEveryStoredValue(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->insert('b');
        $set->insert(3);

        $values = [];
        foreach ($set as $value) {
            $values[] = $value;
        }

        $this->assertEqualsCanonicalizing(['a', 'b', 3], $values);
    }

    public function testForeachOnEmptySetYieldsNothing(): void
    {
        $set = new HashSet();

        $values = [];
        foreach ($set as $value) {
            $values[] = $value;
        }

        $this->assertSame([], $values);
    }

    public function testCountBuiltinReflectsSize(): void
    {
        $set = new HashSet();
        $set->insert('a');
        $set->insert('b');

        $this->assertSame(2, count($set));
        $this->assertSame($set->getSize(), count($set));
    }
}
