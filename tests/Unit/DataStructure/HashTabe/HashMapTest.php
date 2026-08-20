<?php

namespace Tests\Unit\DataStructure\HashTabe;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Contracts\IHashMap;
use Zack\PhpDsAlgo\DataStructure\HashTabe\HashMap;
use Zack\PhpDsAlgo\DataStructure\HashTabe\HashMapNode;

class HashMapTest extends TestCase
{
    /**
     * Mirrors HashMap's private tableHash() so tests can assert exactly
     * which bucket a key is expected to land in.
     */
    private function expectedIndex(string $key, int $capacity): int
    {
        $hash = hash('xxh3', $key);
        $hashInteger = hexdec(substr($hash, 0, 8));

        return $hashInteger % $capacity;
    }

    // --- Construction ---

    public function testConstructWithDefaultCapacity(): void
    {
        $map = new HashMap();

        $this->assertSame(10, $map->getCapacity());
        $this->assertTrue($map->isEmpty());
    }

    public function testConstructWithCustomCapacity(): void
    {
        $map = new HashMap(5);

        $this->assertSame(5, $map->getCapacity());
    }

    public function testConstructThrowsWhenCapacityIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be greater than 0.');

        new HashMap(0);
    }

    public function testConstructThrowsWhenCapacityIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be greater than 0.');

        new HashMap(-3);
    }

    public function testImplementsIHashMap(): void
    {
        $this->assertInstanceOf(IHashMap::class, new HashMap());
    }

    // --- put / hasKey / get ---

    public function testPutMakesKeyAndValueFindable(): void
    {
        $map = new HashMap();

        $map->put('name', 'zack');

        $this->assertTrue($map->hasKey('name'));
        $this->assertSame('zack', $map->get('name'));
    }

    public function testHasKeyReturnsFalseForMissingKey(): void
    {
        $map = new HashMap();

        $this->assertFalse($map->hasKey('missing'));
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        // Regression test: get() must return null for a missing key per
        // IHashMap::get()'s documented `@return V|null`, not throw.
        $map = new HashMap();

        $this->assertNull($map->get('missing'));
    }

    public function testPutReplacesValueForExistingKey(): void
    {
        // Regression test: put() must upsert (insert-or-replace) per
        // IHashMap::put()'s own docblock ("If the key already exists, its
        // associated value is replaced"), not throw on a duplicate key.
        $map = new HashMap();
        $map->put('x', 1);

        $map->put('x', 2);

        $this->assertSame(2, $map->get('x'));
    }

    public function testPutReplacingExistingKeyDoesNotChangeSize(): void
    {
        $map = new HashMap();
        $map->put('x', 1);
        $map->put('y', 2);

        $map->put('x', 99);

        $this->assertSame(2, $map->getSize());
    }

    public function testPutReplacingExistingKeyDoesNotCreateASecondEntry(): void
    {
        $map = new HashMap();
        $map->put('x', 1);

        $map->put('x', 2);

        $this->assertSame(['x'], $map->getAllKeys());
        $this->assertSame([2], $map->getAllValues());
    }

    public function testPutAllowsDifferentKeysWithTheSameValue(): void
    {
        $map = new HashMap();

        $map->put('a', 'dup');
        $map->put('b', 'dup');

        $this->assertSame(2, $map->getSize());
        $this->assertTrue($map->hasKey('a'));
        $this->assertTrue($map->hasKey('b'));
    }

    public function testPutHandlesCollisionsViaChaining(): void
    {
        // 'item-2', 'item-5' and 'item-48' all hash to bucket 25 on a
        // capacity-100 map, exercising separate chaining without ever
        // approaching the 0.7 load factor that would trigger a resize.
        $map = new HashMap(100);

        $map->put('item-2', 'a');
        $map->put('item-5', 'b');
        $map->put('item-48', 'c');

        $this->assertTrue($map->hasKey('item-2'));
        $this->assertTrue($map->hasKey('item-5'));
        $this->assertTrue($map->hasKey('item-48'));
        $this->assertSame('a', $map->get('item-2'));
        $this->assertSame('b', $map->get('item-5'));
        $this->assertSame('c', $map->get('item-48'));
        $this->assertCount(3, $map->getBucket(25));
    }

    public function testPutTriggersResizeWhenLoadFactorExceedsThreshold(): void
    {
        // Capacity 10: the 8th distinct key pushes load factor to 0.8
        // (> 0.7), triggering a resize that doubles the capacity to 20.
        $map = new HashMap(10);

        for ($i = 0; $i < 8; $i++) {
            $map->put("key-{$i}", $i);
        }

        $this->assertSame(20, $map->getCapacity());
        $this->assertSame(8, $map->getSize());
    }

    public function testPutDoesNotResizeBeforeThresholdIsCrossed(): void
    {
        $map = new HashMap(10);

        for ($i = 0; $i < 7; $i++) {
            $map->put("key-{$i}", $i);
        }

        $this->assertSame(10, $map->getCapacity());
    }

    public function testPutReplacingAnExistingKeyNeverTriggersResize(): void
    {
        // Fill to exactly the load-factor threshold (7/10 = 0.7, not
        // strictly greater), then replace an existing key repeatedly —
        // since put() only checks the load factor on a genuine insert,
        // capacity must stay untouched no matter how many replacements follow.
        $map = new HashMap(10);
        for ($i = 0; $i < 7; $i++) {
            $map->put("key-{$i}", $i);
        }

        $map->put('key-0', 'replaced');
        $map->put('key-0', 'replaced-again');

        $this->assertSame(10, $map->getCapacity());
        $this->assertSame(7, $map->getSize());
    }

    public function testAllEntriesRemainFindableAfterResize(): void
    {
        $map = new HashMap(10);

        for ($i = 0; $i < 8; $i++) {
            $map->put("key-{$i}", "value-{$i}");
        }

        for ($i = 0; $i < 8; $i++) {
            $this->assertSame("value-{$i}", $map->get("key-{$i}"));
        }
    }

    // --- delete ---

    public function testDeleteRemovesExistingKey(): void
    {
        $map = new HashMap();
        $map->put('gone', 1);

        $result = $map->delete('gone');

        $this->assertTrue($result);
        $this->assertFalse($map->hasKey('gone'));
        $this->assertNull($map->get('gone'));
    }

    public function testDeleteReturnsFalseForMissingKey(): void
    {
        $map = new HashMap();

        $this->assertFalse($map->delete('missing'));
    }

    public function testDeleteReducesSize(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);

        $map->delete('a');

        $this->assertSame(1, $map->getSize());
    }

    public function testDeleteReindexesRemainingEntriesInTheBucket(): void
    {
        // Unlike HashTable::delete() (which uses unset() and leaves a gap),
        // HashMap::delete() uses array_splice(), so a bucket's keys stay a
        // contiguous 0-based list after a deletion.
        $map = new HashMap(100);
        $map->put('item-2', 'a');
        $map->put('item-5', 'b');
        $map->put('item-48', 'c');

        $map->delete('item-2');

        $bucket = $map->getBucket(25);
        $this->assertSame([0, 1], array_keys($bucket));
        $this->assertSame('item-5', $bucket[0]->getKey());
        $this->assertSame('item-48', $bucket[1]->getKey());
    }

    public function testDeleteFromEmptyMapReturnsFalse(): void
    {
        $map = new HashMap();

        $this->assertFalse($map->delete('anything'));
    }

    // --- update ---

    public function testUpdateReplacesValueForExistingKey(): void
    {
        $map = new HashMap();
        $map->put('x', 'old');

        $result = $map->update('x', 'new');

        $this->assertTrue($result);
        $this->assertSame('new', $map->get('x'));
    }

    public function testUpdateReturnsFalseWhenKeyMissing(): void
    {
        $map = new HashMap();

        $this->assertFalse($map->update('missing', 'value'));
    }

    public function testUpdateDoesNotInsertAMissingKey(): void
    {
        // Unlike put(), update() never creates a new entry.
        $map = new HashMap();

        $map->update('missing', 'value');

        $this->assertFalse($map->hasKey('missing'));
        $this->assertSame(0, $map->getSize());
    }

    public function testUpdateKeepsSizeUnchanged(): void
    {
        $map = new HashMap();
        $map->put('x', 1);
        $map->put('y', 2);

        $map->update('x', 99);

        $this->assertSame(2, $map->getSize());
    }

    // --- hasValue ---

    public function testHasValueReturnsTrueWhenValuePresent(): void
    {
        $map = new HashMap();
        $map->put('x', 'findme');

        $this->assertTrue($map->hasValue('findme'));
    }

    public function testHasValueReturnsFalseWhenValueAbsent(): void
    {
        $map = new HashMap();
        $map->put('x', 'a');

        $this->assertFalse($map->hasValue('missing'));
    }

    public function testHasValueUsesStrictComparison(): void
    {
        $map = new HashMap();
        $map->put('x', 1);

        $this->assertFalse($map->hasValue('1'));
        $this->assertFalse($map->hasValue(true));
    }

    // --- getKeyPosition / getValuePosition ---

    public function testGetKeyPositionReturnsBucketAndItemIndex(): void
    {
        $map = new HashMap(10);
        $key = 'positioned';
        $map->put($key, 'v');

        $expectedBucket = $this->expectedIndex($key, 10);
        $this->assertSame(
            ['bucketIndex' => $expectedBucket, 'itemIndex' => 0],
            $map->getKeyPosition($key)
        );
    }

    public function testGetKeyPositionReturnsFalseForMissingKey(): void
    {
        $map = new HashMap();

        $this->assertFalse($map->getKeyPosition('missing'));
    }

    public function testGetValuePositionReturnsBucketAndItemIndex(): void
    {
        $map = new HashMap(100);
        $map->put('item-2', 'a');
        $map->put('item-5', 'b');

        $this->assertSame(
            ['bucketIndex' => 25, 'itemIndex' => 1],
            $map->getValuePosition('b')
        );
    }

    public function testGetValuePositionReturnsFalseForMissingValue(): void
    {
        $map = new HashMap();

        $this->assertFalse($map->getValuePosition('missing'));
    }

    // --- getAllEntries / getAllKeys / getAllValues ---

    public function testGetAllEntriesReturnsEmptyArrayForEmptyMap(): void
    {
        $map = new HashMap();

        $this->assertSame([], $map->getAllEntries());
    }

    public function testGetAllEntriesReturnsHashMapNodesWithMatchingKeysAndValues(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);

        $entries = $map->getAllEntries();

        $this->assertCount(2, $entries);
        foreach ($entries as $entry) {
            $this->assertInstanceOf(HashMapNode::class, $entry);
        }
        $pairs = array_map(
            fn(HashMapNode $node) => [$node->getKey(), $node->getValue()],
            $entries
        );
        $this->assertEqualsCanonicalizing([['a', 1], ['b', 2]], $pairs);
    }

    public function testGetAllKeysReturnsEveryKey(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);

        $this->assertEqualsCanonicalizing(['a', 'b'], $map->getAllKeys());
    }

    public function testGetAllValuesReturnsEveryValue(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);

        $this->assertEqualsCanonicalizing([1, 2], $map->getAllValues());
    }

    public function testGetAllKeysExcludesDeletedKeys(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);
        $map->delete('a');

        $this->assertSame(['b'], $map->getAllKeys());
    }

    // --- getBuckets / getBucket ---

    public function testGetBucketsReturnsIndexForEveryBucket(): void
    {
        $map = new HashMap(4);

        $this->assertSame([0, 1, 2, 3], $map->getBuckets());
    }

    public function testGetBucketReturnsEmptyArrayForUnusedBucket(): void
    {
        $map = new HashMap(4);

        $this->assertSame([], $map->getBucket(0));
    }

    public function testGetBucketThrowsForOutOfRangeIndex(): void
    {
        $map = new HashMap(4);

        $this->expectException(OutOfBoundsException::class);

        $map->getBucket(4);
    }

    public function testGetBucketThrowsForNegativeIndex(): void
    {
        $map = new HashMap(4);

        $this->expectException(OutOfBoundsException::class);

        $map->getBucket(-1);
    }

    // --- getSize / getCapacity / getLoadFactor ---

    public function testGetSizeReturnsZeroForEmptyMap(): void
    {
        $this->assertSame(0, (new HashMap())->getSize());
    }

    public function testGetSizeCountsAcrossAllBuckets(): void
    {
        $map = new HashMap(100);
        $map->put('item-2', 'a');
        $map->put('item-5', 'b');
        $map->put('item-48', 'c');

        $this->assertSame(3, $map->getSize());
    }

    public function testGetCapacityReturnsConfiguredCapacity(): void
    {
        $this->assertSame(7, (new HashMap(7))->getCapacity());
    }

    public function testGetLoadFactorReflectsSizeOverCapacity(): void
    {
        $map = new HashMap(4);
        $map->put('a', 1);
        $map->put('b', 2);

        $this->assertSame(0.5, $map->getLoadFactor());
    }

    public function testGetLoadFactorIsZeroForEmptyMap(): void
    {
        $this->assertSame(0.0, (new HashMap())->getLoadFactor());
    }

    // --- resize ---

    public function testResizeDoublesCapacity(): void
    {
        $map = new HashMap(4);

        $map->resize();

        $this->assertSame(8, $map->getCapacity());
    }

    public function testResizePreservesAllEntries(): void
    {
        $map = new HashMap(4);
        $map->put('a', 1);
        $map->put('b', 2);
        $map->put('c', 3);

        $map->resize();

        $this->assertEqualsCanonicalizing(['a', 'b', 'c'], $map->getAllKeys());
        $this->assertSame(3, $map->getSize());
        $this->assertSame(1, $map->get('a'));
    }

    // --- clear ---

    public function testClearEmptiesTheMap(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);

        $map->clear();

        $this->assertTrue($map->isEmpty());
        $this->assertSame(0, $map->getSize());
        $this->assertSame([], $map->getAllEntries());
    }

    public function testClearPreservesCapacity(): void
    {
        $map = new HashMap(20);
        $map->put('a', 1);

        $map->clear();

        $this->assertSame(20, $map->getCapacity());
    }

    // --- reset ---

    public function testResetRestoresDefaultCapacity(): void
    {
        $map = new HashMap(50);
        $map->put('a', 1);

        $map->reset();

        $this->assertSame(10, $map->getCapacity());
    }

    public function testResetEmptiesTheMap(): void
    {
        $map = new HashMap(50);
        $map->put('a', 1);
        $map->put('b', 2);

        $map->reset();

        $this->assertTrue($map->isEmpty());
        $this->assertSame(0, $map->getSize());
    }

    // --- isEmpty ---

    public function testIsEmptyReturnsTrueForNewMap(): void
    {
        $this->assertTrue((new HashMap())->isEmpty());
    }

    public function testIsEmptyReturnsFalseAfterPut(): void
    {
        $map = new HashMap();
        $map->put('a', 1);

        $this->assertFalse($map->isEmpty());
    }

    public function testIsEmptyReturnsTrueAfterDeletingOnlyKey(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->delete('a');

        $this->assertTrue($map->isEmpty());
    }

    // --- Generic (non-string) keys and values ---

    public function testPutAndGetWorkWithIntegerKeys(): void
    {
        $map = new HashMap();

        $map->put(42, 'answer');

        $this->assertTrue($map->hasKey(42));
        $this->assertSame('answer', $map->get(42));
    }

    public function testHasKeyDistinguishesKeysByTypeNotJustLooseEquality(): void
    {
        // hasKey() compares with ===, so the int 1, the string "1" and
        // true must all be tracked as distinct keys.
        $map = new HashMap();
        $map->put(1, 'int-one');

        $this->assertTrue($map->hasKey(1));
        $this->assertFalse($map->hasKey('1'));
        $this->assertFalse($map->hasKey(true));
    }

    public function testPutAndGetWorkWithArrayKeys(): void
    {
        $map = new HashMap();
        $key = ['id' => 1, 'name' => 'a'];

        $map->put($key, 'matched');

        $this->assertSame('matched', $map->get($key));
        $this->assertNull($map->get(['id' => 2, 'name' => 'b']));
    }

    public function testPutAndGetWorkWithObjectValues(): void
    {
        $map = new HashMap();
        $value = new \stdClass();
        $value->name = 'payload';

        $map->put('key', $value);

        $this->assertSame($value, $map->get('key'));
    }

    public function testHasKeyUsesIdentityForObjectKeysWithoutValueEquality(): void
    {
        // Two distinct stdClass instances with the same property serialize
        // identically (same bucket), but === still distinguishes them by
        // identity, exercising the collision-chaining path for object keys.
        $map = new HashMap();
        $a = new \stdClass();
        $a->name = 'same';
        $b = new \stdClass();
        $b->name = 'same';

        $map->put($a, 'value-for-a');

        $this->assertTrue($map->hasKey($a));
        $this->assertFalse($map->hasKey($b));
    }

    public function testPutThrowsForClosureKeys(): void
    {
        $map = new HashMap();

        $this->expectException(InvalidArgumentException::class);

        $map->put(fn() => 1, 'value');
    }

    public function testPutThrowsForResourceKeys(): void
    {
        $map = new HashMap();
        $resource = fopen('php://memory', 'r');

        try {
            $this->expectException(InvalidArgumentException::class);

            $map->put($resource, 'value');
        } finally {
            fclose($resource);
        }
    }

    // --- Negative zero regression ---

    public function testHasKeyTreatsNegativeZeroAsEqualToPositiveZero(): void
    {
        // Regression test: `-0.0 === 0.0` is true in PHP (the comparison
        // this class uses for key equality), but serialize(-0.0) and
        // serialize(0.0) produce different strings ("d:-0;" vs "d:0;").
        // Without normalizing -0.0 before hashing, the two values would
        // land in different buckets and hasKey(0.0) would incorrectly
        // report a key stored as -0.0 as absent.
        $map = new HashMap();
        $map->put(-0.0, 'value');

        $this->assertTrue($map->hasKey(0.0));
        $this->assertSame('value', $map->get(0.0));
    }

    public function testPutWithPositiveZeroReplacesKeyStoredAsNegativeZero(): void
    {
        $map = new HashMap();
        $map->put(-0.0, 'first');

        $map->put(0.0, 'second');

        $this->assertSame(1, $map->getSize());
        $this->assertSame('second', $map->get(-0.0));
    }

    // --- IteratorAggregate / Countable ---

    public function testForeachYieldsEveryKeyValuePair(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);

        $pairs = [];
        foreach ($map as $key => $value) {
            $pairs[$key] = $value;
        }

        $this->assertSame(['a' => 1, 'b' => 2], $pairs);
    }

    public function testForeachYieldsNonScalarKeysUnchanged(): void
    {
        // Generator keys aren't restricted to int|string the way a native
        // PHP array's keys are, so a HashMap with an array key must survive
        // a foreach without the key being coerced or dropped.
        $map = new HashMap();
        $arrayKey = ['id' => 1];
        $map->put($arrayKey, 'value');

        $seenKeys = [];
        foreach ($map as $key => $value) {
            $seenKeys[] = $key;
        }

        $this->assertSame([$arrayKey], $seenKeys);
    }

    public function testForeachOnEmptyMapYieldsNothing(): void
    {
        $map = new HashMap();

        $pairs = [];
        foreach ($map as $key => $value) {
            $pairs[$key] = $value;
        }

        $this->assertSame([], $pairs);
    }

    public function testCountBuiltinReflectsSize(): void
    {
        $map = new HashMap();
        $map->put('a', 1);
        $map->put('b', 2);

        $this->assertSame(2, count($map));
        $this->assertSame($map->getSize(), count($map));
    }
}
