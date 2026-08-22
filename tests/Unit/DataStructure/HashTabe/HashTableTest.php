<?php

namespace Tests\Unit\DataStructure\HashTabe;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Contracts\IHashTable;
use Zack\PhpDsAlgo\DataStructure\HashTabe\HashTable;

class HashTableTest extends TestCase
{
    /**
     * Mirrors HashTable's private tableHash() so tests can assert exactly
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
        $table = new HashTable();

        $this->assertSame(10, $table->getCapacity());
        $this->assertTrue($table->isEmpty());
    }

    public function testConstructWithCustomCapacity(): void
    {
        $table = new HashTable(5);

        $this->assertSame(5, $table->getCapacity());
    }

    public function testConstructThrowsWhenCapacityIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be greater than 0.');

        new HashTable(0);
    }

    public function testConstructThrowsWhenCapacityIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be greater than 0.');

        new HashTable(-3);
    }

    public function testImplementsIHashTable(): void
    {
        $this->assertInstanceOf(IHashTable::class, new HashTable());
    }

    // --- insert / hasValue ---

    public function testInsertMakesValueFindable(): void
    {
        $table = new HashTable();

        $table->insert('hello');

        $this->assertTrue($table->hasValue('hello'));
    }

    public function testHasValueReturnsFalseForMissingValue(): void
    {
        $table = new HashTable();

        $this->assertFalse($table->hasValue('missing'));
    }

    public function testInsertPlacesValueInExpectedBucket(): void
    {
        $table = new HashTable(10);
        $value = 'bucket-target';

        $table->insert($value);

        $expected = $this->expectedIndex($value, 10);
        $this->assertSame(
            ['bucketIndex' => $expected, 'itemIndex' => 0],
            $table->getValuePosition($value)
        );
    }

    public function testInsertHandlesCollisionsViaChaining(): void
    {
        // 'item-2', 'item-5' and 'item-48' all hash to bucket 25 on a
        // capacity-100 table, exercising separate chaining without ever
        // approaching the 0.7 load factor that would trigger a resize.
        $table = new HashTable(100);

        $table->insert('item-2');
        $table->insert('item-5');
        $table->insert('item-48');

        $this->assertTrue($table->hasValue('item-2'));
        $this->assertTrue($table->hasValue('item-5'));
        $this->assertTrue($table->hasValue('item-48'));
        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 0],
            $table->getValuePosition('item-2')
        );
        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 1],
            $table->getValuePosition('item-5')
        );
        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 2],
            $table->getValuePosition('item-48')
        );
    }

    public function testInsertAllowsDuplicateValues(): void
    {
        $table = new HashTable();

        $table->insert('dup');
        $table->insert('dup');

        $this->assertSame(2, $table->getSize());
    }

    public function testInsertTriggersResizeWhenLoadFactorExceedsThreshold(): void
    {
        // Capacity 10: the 8th insert pushes load factor to 0.8 (> 0.7),
        // triggering a resize that doubles the capacity to 20.
        $table = new HashTable(10);

        for ($i = 0; $i < 8; $i++) {
            $table->insert("value-{$i}");
        }

        $this->assertSame(20, $table->getCapacity());
        $this->assertSame(8, $table->getSize());
    }

    public function testInsertDoesNotResizeBeforeThresholdIsCrossed(): void
    {
        // 7 inserts on capacity 10 gives a load factor of exactly 0.7,
        // which is not strictly greater than 0.7.
        $table = new HashTable(10);

        for ($i = 0; $i < 7; $i++) {
            $table->insert("value-{$i}");
        }

        $this->assertSame(10, $table->getCapacity());
    }

    public function testAllValuesRemainFindableAfterResize(): void
    {
        $table = new HashTable(10);

        $values = [];
        for ($i = 0; $i < 8; $i++) {
            $values[] = "value-{$i}";
            $table->insert("value-{$i}");
        }

        foreach ($values as $value) {
            $this->assertTrue($table->hasValue($value));
        }
    }

    // --- delete ---

    public function testDeleteRemovesExistingValue(): void
    {
        $table = new HashTable();
        $table->insert('gone');

        $result = $table->delete('gone');

        $this->assertTrue($result);
        $this->assertFalse($table->hasValue('gone'));
    }

    public function testDeleteReturnsFalseForMissingValue(): void
    {
        $table = new HashTable();

        $this->assertFalse($table->delete('missing'));
    }

    public function testDeleteReducesSize(): void
    {
        $table = new HashTable();
        $table->insert('a');
        $table->insert('b');

        $table->delete('a');

        $this->assertSame(1, $table->getSize());
    }

    public function testDeleteOnlyRemovesOneOfDuplicateValues(): void
    {
        $table = new HashTable();
        $table->insert('dup');
        $table->insert('dup');

        $table->delete('dup');

        $this->assertTrue($table->hasValue('dup'));
        $this->assertSame(1, $table->getSize());
    }

    public function testDeleteFromEmptyTableReturnsFalse(): void
    {
        $table = new HashTable();

        $this->assertFalse($table->delete('anything'));
    }

    // --- getValuePosition ---

    public function testGetValuePositionReturnsBucketAndItemIndex(): void
    {
        $table = new HashTable(10);
        $value = 'positioned';

        $table->insert($value);

        $expectedBucket = $this->expectedIndex($value, 10);
        $this->assertSame(
            ['bucketIndex' => $expectedBucket, 'itemIndex' => 0],
            $table->getValuePosition($value)
        );
    }

    public function testGetValuePositionReturnsFalseForMissingValue(): void
    {
        $table = new HashTable();

        $this->assertFalse($table->getValuePosition('missing'));
    }

    public function testGetValuePositionReflectsItemIndexWithinBucket(): void
    {
        // 'item-2' and 'item-5' collide into bucket 25 on a capacity-100 table.
        $table = new HashTable(100);
        $table->insert('item-2');
        $table->insert('item-5');

        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 1],
            $table->getValuePosition('item-5')
        );
    }

    // --- update ---

    public function testUpdateReplacesExistingValue(): void
    {
        $table = new HashTable();
        $table->insert('old');

        $result = $table->update('old', 'new');

        $this->assertTrue($result);
        $this->assertFalse($table->hasValue('old'));
        $this->assertTrue($table->hasValue('new'));
    }

    public function testUpdateReturnsFalseWhenOldValueMissing(): void
    {
        $table = new HashTable();

        $this->assertFalse($table->update('missing', 'new'));
    }

    public function testUpdateKeepsSizeUnchanged(): void
    {
        $table = new HashTable();
        $table->insert('old');
        $table->insert('other');

        $table->update('old', 'new');

        $this->assertSame(2, $table->getSize());
    }

    public function testUpdateMovesValueToNewValuesBucket(): void
    {
        $table = new HashTable(10);
        $table->insert('old');

        $table->update('old', 'new');

        $expected = $this->expectedIndex('new', 10);
        $this->assertSame(
            ['bucketIndex' => $expected, 'itemIndex' => 0],
            $table->getValuePosition('new')
        );
    }

    // --- getValue ---

    public function testGetValueReturnsValueWhenPresent(): void
    {
        $table = new HashTable();
        $table->insert('present');

        $this->assertSame('present', $table->getValue('present'));
    }

    public function testGetValueReturnsNullWhenMissing(): void
    {
        $table = new HashTable();

        $this->assertNull($table->getValue('missing'));
    }

    // --- getAllValues ---

    public function testGetAllValuesReturnsEmptyArrayForEmptyTable(): void
    {
        $table = new HashTable();

        $this->assertSame([], $table->getAllValues());
    }

    public function testGetAllValuesReturnsEveryInsertedValue(): void
    {
        $table = new HashTable();
        $table->insert('a');
        $table->insert('b');
        $table->insert('c');

        $this->assertEqualsCanonicalizing(['a', 'b', 'c'], $table->getAllValues());
    }

    public function testGetAllValuesExcludesDeletedValues(): void
    {
        $table = new HashTable();
        $table->insert('a');
        $table->insert('b');
        $table->delete('a');

        $this->assertSame(['b'], $table->getAllValues());
    }

    // --- getSize / getCapacity / getLoadFactor ---

    public function testGetSizeReturnsZeroForEmptyTable(): void
    {
        $this->assertSame(0, (new HashTable())->getSize());
    }

    public function testGetSizeCountsAcrossAllBuckets(): void
    {
        $table = new HashTable(1);
        $table->insert('a');
        $table->insert('b');
        $table->insert('c');

        $this->assertSame(3, $table->getSize());
    }

    public function testGetCapacityReturnsConfiguredCapacity(): void
    {
        $this->assertSame(7, (new HashTable(7))->getCapacity());
    }

    public function testGetLoadFactorReflectsSizeOverCapacity(): void
    {
        $table = new HashTable(4);
        $table->insert('a');
        $table->insert('b');

        $this->assertSame(0.5, $table->getLoadFactor());
    }

    public function testGetLoadFactorIsZeroForEmptyTable(): void
    {
        $this->assertSame(0.0, (new HashTable())->getLoadFactor());
    }

    // --- resize ---

    public function testResizeDoublesCapacity(): void
    {
        $table = new HashTable(4);

        $table->resize();

        $this->assertSame(8, $table->getCapacity());
    }

    public function testResizePreservesAllValues(): void
    {
        $table = new HashTable(4);
        $table->insert('a');
        $table->insert('b');
        $table->insert('c');

        $table->resize();

        $this->assertEqualsCanonicalizing(['a', 'b', 'c'], $table->getAllValues());
        $this->assertSame(3, $table->getSize());
    }

    public function testResizeRebucketsValuesByNewCapacity(): void
    {
        $table = new HashTable(4);
        $value = 'rebucketed';
        $table->insert($value);

        $table->resize();

        $expected = $this->expectedIndex($value, 8);
        $this->assertSame(
            ['bucketIndex' => $expected, 'itemIndex' => 0],
            $table->getValuePosition($value)
        );
    }

    // --- clear ---

    public function testClearEmptiesTheTable(): void
    {
        $table = new HashTable();
        $table->insert('a');
        $table->insert('b');

        $table->clear();

        $this->assertTrue($table->isEmpty());
        $this->assertSame(0, $table->getSize());
        $this->assertSame([], $table->getAllValues());
    }

    public function testClearPreservesCapacity(): void
    {
        $table = new HashTable(20);
        $table->insert('a');

        $table->clear();

        $this->assertSame(20, $table->getCapacity());
    }

    // --- reset ---

    public function testResetRestoresDefaultCapacity(): void
    {
        $table = new HashTable(50);
        $table->insert('a');

        $table->reset();

        $this->assertSame(10, $table->getCapacity());
    }

    public function testResetEmptiesTheTable(): void
    {
        $table = new HashTable(50);
        $table->insert('a');
        $table->insert('b');

        $table->reset();

        $this->assertTrue($table->isEmpty());
        $this->assertSame(0, $table->getSize());
    }

    // --- isEmpty ---

    public function testIsEmptyReturnsTrueForNewTable(): void
    {
        $this->assertTrue((new HashTable())->isEmpty());
    }

    public function testIsEmptyReturnsFalseAfterInsert(): void
    {
        $table = new HashTable();
        $table->insert('a');

        $this->assertFalse($table->isEmpty());
    }

    public function testIsEmptyReturnsTrueAfterDeletingOnlyValue(): void
    {
        $table = new HashTable();
        $table->insert('a');
        $table->delete('a');

        $this->assertTrue($table->isEmpty());
    }

    // --- Generic (non-string) values ---

    public function testInsertAndHasValueWorkWithIntegers(): void
    {
        $table = new HashTable();

        $table->insert(42);

        $this->assertTrue($table->hasValue(42));
        $this->assertFalse($table->hasValue(43));
    }

    public function testInsertAndHasValueWorkWithFloats(): void
    {
        $table = new HashTable();

        $table->insert(3.14);

        $this->assertTrue($table->hasValue(3.14));
    }

    public function testInsertAndHasValueWorkWithBooleansAndNull(): void
    {
        $table = new HashTable();

        $table->insert(true);
        $table->insert(false);
        $table->insert(null);

        $this->assertTrue($table->hasValue(true));
        $this->assertTrue($table->hasValue(false));
        $this->assertTrue($table->hasValue(null));
    }

    public function testHasValueDistinguishesValuesByTypeNotJustLooseEquality(): void
    {
        // hasValue() compares with ===, so the int 1, the string "1" and
        // true must all be tracked as distinct entries even if two of them
        // land in the same bucket.
        $table = new HashTable();
        $table->insert(1);

        $this->assertTrue($table->hasValue(1));
        $this->assertFalse($table->hasValue('1'));
        $this->assertFalse($table->hasValue(true));
    }

    public function testInsertAndHasValueWorkWithArrays(): void
    {
        $table = new HashTable();
        $value = ['id' => 1, 'name' => 'a'];

        $table->insert($value);

        $this->assertTrue($table->hasValue($value));
        $this->assertFalse($table->hasValue(['id' => 2, 'name' => 'b']));
    }

    public function testInsertAndHasValueWorkWithObjects(): void
    {
        // hasValue() compares with ===, which for objects is identity, not
        // value equality - a clone with identical properties is a
        // different instance and must not be reported as present.
        $table = new HashTable();
        $value = new \DateTimeImmutable('2024-01-01');

        $table->insert($value);

        $this->assertTrue($table->hasValue($value));
        $this->assertFalse($table->hasValue(clone $value));
    }

    public function testHasValueUsesIdentityForObjectsWithoutValueEquality(): void
    {
        // Two distinct stdClass instances with the same property serialize
        // identically (same bucket), but === still distinguishes them by
        // identity, exercising the collision-chaining path for objects.
        $table = new HashTable();
        $a = new \stdClass();
        $a->name = 'same';
        $b = new \stdClass();
        $b->name = 'same';

        $table->insert($a);

        $this->assertTrue($table->hasValue($a));
        $this->assertFalse($table->hasValue($b));
    }

    public function testInsertThrowsForClosures(): void
    {
        $table = new HashTable();

        $this->expectException(InvalidArgumentException::class);

        $table->insert(fn () => 1);
    }

    public function testInsertThrowsForResources(): void
    {
        $table = new HashTable();
        $resource = fopen('php://memory', 'r');

        try {
            $this->expectException(InvalidArgumentException::class);

            $table->insert($resource);
        } finally {
            fclose($resource);
        }
    }

    public function testGetAllValuesPreservesMixedTypes(): void
    {
        $table = new HashTable();
        $table->insert(1);
        $table->insert('1');
        $table->insert(1.0);

        $this->assertEqualsCanonicalizing([1, '1', 1.0], $table->getAllValues());
    }

    public function testHasValueTreatsNegativeZeroAsEqualToPositiveZero(): void
    {
        // Regression test: `-0.0 === 0.0` is true in PHP (the comparison
        // this class uses for equality), but serialize(-0.0) and
        // serialize(0.0) produce different strings ("d:-0;" vs "d:0;").
        // Without normalizing -0.0 before hashing, the two values would
        // land in different buckets and hasValue(0.0) would incorrectly
        // report a stored -0.0 as absent.
        $table = new HashTable();
        $table->insert(-0.0);

        $this->assertTrue($table->hasValue(0.0));
        $this->assertTrue($table->hasValue(-0.0));
    }

    public function testDeleteFindsValueInsertedAsNegativeZero(): void
    {
        $table = new HashTable();
        $table->insert(-0.0);

        $this->assertTrue($table->delete(0.0));
        $this->assertTrue($table->isEmpty());
    }

    public function testInsertNegativeZeroAndPositiveZeroLandInTheSameBucket(): void
    {
        $table = new HashTable(10);

        $table->insert(-0.0);
        $table->insert(0.0);

        // Both values land in the same bucket and are tracked as two
        // separate entries there (duplicates are allowed) - getValuePosition()
        // can't tell the two apart by === (they compare equal), but the
        // bucket they're placed in matches, and getSize() still counts both.
        $negativeZeroPosition = $table->getValuePosition(-0.0);
        $positiveZeroPosition = $table->getValuePosition(0.0);

        $this->assertSame($negativeZeroPosition['bucketIndex'], $positiveZeroPosition['bucketIndex']);
        $this->assertSame(2, $table->getSize());
    }

    // --- IteratorAggregate / Countable ---

    public function testForeachYieldsEveryStoredValue(): void
    {
        $table = new HashTable();
        $table->insert('a');
        $table->insert('b');
        $table->insert(3);

        $values = [];
        foreach ($table as $value) {
            $values[] = $value;
        }

        $this->assertEqualsCanonicalizing(['a', 'b', 3], $values);
    }

    public function testForeachOnEmptyTableYieldsNothing(): void
    {
        $table = new HashTable();

        $values = [];
        foreach ($table as $value) {
            $values[] = $value;
        }

        $this->assertSame([], $values);
    }

    public function testCountBuiltinReflectsSize(): void
    {
        $table = new HashTable();
        $table->insert('a');
        $table->insert('b');

        $this->assertSame(2, count($table));
        $this->assertSame($table->getSize(), count($table));
    }
}
