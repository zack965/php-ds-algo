<?php

namespace Zack\PhpDsAlgo\DataStructure\HashTabe;

use InvalidArgumentException;
use Zack\PhpDsAlgo\Contracts\IHashTable;

/**
 * @template T
 *
 * @implements IHashTable<T>
 */
class HashTable implements IHashTable
{
    /**
     * Array of buckets containing hashed values.
     *
     * Each bucket is keyed by int but is not guaranteed to be a contiguous
     * list: {@see delete()} uses `unset()` rather than reindexing, so a
     * bucket's keys can have gaps after a deletion.
     *
     * @var array<int, array<int, T>>
     */
    private array $table;


    /**
     * Creates an empty hash table with the given number of buckets.
     *
     * @param int $capacity Number of buckets in the hash table.
     *
     * @throws InvalidArgumentException If the capacity is less than or equal to zero.
     */
    public function __construct(
        private int $capacity = 10,
    ) {
        if ($this->capacity <= 0) {
            throw new InvalidArgumentException(
                'Capacity must be greater than 0.'
            );
        }

        /** @var array<int, array<int, T>> $table */
        $table = array_fill(0, $this->capacity, []);

        $this->table = $table;
    }

    /**
     * Inserts a value into the hash table.
     *
     * The value is reduced to a hashable string key (see {@see hashKey()}),
     * hashed using PHP's xxh3 hashing algorithm, and mapped to a bucket index.
     *
     * Collisions are handled using separate chaining, meaning
     * multiple values can exist inside the same bucket. Duplicate values
     * are allowed and are stored as separate entries.
     *
     * After the value is added, if the resulting load factor
     * (`getSize() / getCapacity()`) exceeds 0.7, the table is automatically
     * grown via {@see resize()}.
     *
     * @param T $value The value to insert.
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     *
     * @return void
     */
    public function insert(mixed $value): void
    {
        $this->addToBucket($value);


        $size = $this->getSize();
        $loadFactor = $size / $this->capacity;

        if ($loadFactor > 0.7) {
            $this->resize();
        }
    }

    /**
     * Reduces a value of any hashable type to the string that {@see tableHash()}
     * hashes on.
     *
     * Hashable values are strings, other scalars (int, float, bool), null,
     * arrays, and objects that PHP can serialize (i.e. not closures). Two
     * values that are `===` equal always produce the same key, but the key
     * is only used to pick a bucket - actual lookups still compare values
     * with `===`, so unrelated values that happen to share a key (a hash
     * collision) are handled correctly via chaining.
     *
     * Resources and closures have no stable value representation and are
     * rejected.
     *
     * @param T $value
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     */
    private function hashKey(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_resource($value)) {
            throw new InvalidArgumentException(
                'Resources cannot be used as hash table values.'
            );
        }

        try {
            return serialize($value);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(
                sprintf('Value of type %s cannot be hashed.', get_debug_type($value)),
                previous: $e
            );
        }
    }

    /**
     * Computes the bucket index a value belongs to.
     *
     * Hashes {@see hashKey()}'s string representation of the value with
     * xxh3, takes the first 8 hex characters as an integer, and reduces it
     * modulo the current capacity.
     *
     * @param T $value
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     */
    private function tableHash(mixed $value): int
    {
        $hash = hash('xxh3', $this->hashKey($value));

        // Convert the hexadecimal hash into an integer.
        $hashInteger = hexdec(substr($hash, 0, 8));

        // Map the hash to a valid bucket index.
        return $hashInteger % $this->capacity;
    }

    /**
     * Removes the first occurrence of a value from the hash table.
     *
     * Looks up the value's bucket, then scans it for a `===` match. If a
     * duplicate value was inserted multiple times, only one occurrence is
     * removed.
     *
     * @param T $value
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     *
     * @return bool True if a matching value was found and removed, false otherwise.
     */
    public function delete(mixed $value): bool
    {
        $index = $this->tableHash($value);
        foreach ($this->table[$index] as $itemIndex => $item) {
            if ($item === $value) {
                unset($this->table[$index][$itemIndex]);


                return true;
            }
        }
        return false;
    }

    /**
     * Determines whether a value exists in the hash table.
     *
     * Looks up the value's bucket, then scans it for a `===` match.
     *
     * @param T $value
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     *
     * @return bool True if the value is present, false otherwise.
     */
    public function hasValue(mixed $value): bool
    {
        $index = $this->tableHash($value);
        foreach ($this->table[$index] as $itemIndex => $item) {
            if ($item === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Locates a value's storage position within the table.
     *
     * @param T $value
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     *
     * @return array{bucketIndex: int, itemIndex: int}|false The bucket index
     *  and the key within that bucket's array where the value is stored, or
     *  false if the value isn't present.
     */
    public function getValuePosition(mixed $value): bool|array
    {
        if ($this->hasValue($value)) {
            $index = $this->tableHash($value);
            foreach ($this->table[$index] as $itemIndex => $item) {
                if ($item === $value) {

                    return [
                        "bucketIndex" => $index,
                        "itemIndex" => $itemIndex
                    ];
                }
            }
        }
        return false;
    }

    /**
     * Replaces an existing value with a new one.
     *
     * Removes `$oldValue` from its current bucket and inserts `$newValue`
     * into whichever bucket it hashes to (which may differ from the old
     * bucket). This does not go through {@see insert()}, so it never
     * triggers a resize.
     *
     * @param T $oldValue The value to look up and remove.
     * @param T $newValue The value to store in its place.
     *
     * @throws InvalidArgumentException If either value cannot be hashed.
     *
     * @return bool True if `$oldValue` was found and replaced, false otherwise.
     */
    public function update(mixed $oldValue, mixed $newValue): bool
    {
        $position = $this->getValuePosition($oldValue);

        if ($position === false) {
            return false;
        }

        unset(
            $this->table[$position['bucketIndex']][$position['itemIndex']]
        );

        $newIndex = $this->tableHash($newValue);

        $this->table[$newIndex][] = $newValue;

        return true;
    }

    /**
     * Returns the current number of buckets in the table.
     *
     * This grows over time as {@see resize()} runs (automatically from
     * {@see insert()}, or manually), and resets to 10 via {@see reset()}.
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * Returns the total number of values currently stored, summed across
     * all buckets.
     */
    public function getSize(): int
    {
        $size = 0;

        foreach ($this->table as $bucket) {
            $size += count($bucket);
        }

        return $size;
    }

    /**
     * Appends a value directly into the bucket it hashes to.
     *
     * Unlike {@see insert()}, this never checks the load factor or
     * triggers a resize; it's the shared primitive both {@see insert()}
     * and {@see resize()} build on.
     *
     * @param T $value
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     */
    private function addToBucket(mixed $value): void
    {
        $index = $this->tableHash($value);

        $this->table[$index][] = $value;
    }

    /**
     * Grows the table to make room for more values.
     *
     * Doubles the capacity, then rebuilds the table from scratch and
     * re-inserts every existing value, so each one is re-bucketed against
     * the new capacity (bucket assignments are not stable across a resize).
     *
     * Called automatically by {@see insert()} once the load factor exceeds
     * 0.7; can also be called directly to grow the table pre-emptively.
     */
    public function resize(): void
    {

        $values = $this->getAllValues();

        $this->capacity *= 2;

        $this->table = array_fill(0, $this->capacity, []);

        foreach ($values as $value) {
            $this->addToBucket($value);
        }
    }

    /**
     * Removes every stored value, leaving the current capacity unchanged.
     */
    public function clear(): void
    {
        $this->table = array_fill(0, $this->capacity, []);
    }

    /**
     * Removes every stored value and restores the default capacity (10),
     * undoing any growth from {@see resize()}.
     */
    public function reset(): void
    {
        $this->capacity = 10;
        $this->table = array_fill(0, $this->capacity, []);
    }

    /**
     * Retrieves a stored value.
     *
     * Since values act as their own keys in this hash table, this simply
     * confirms membership and hands the same value back - it's useful
     * mainly for the null-if-missing convenience over {@see hasValue()}.
     *
     * @param T $value
     *
     * @throws InvalidArgumentException If the value cannot be hashed.
     *
     * @return T|null The value itself if present, null otherwise.
     */
    public function getValue(mixed $value): mixed
    {
        return $this->hasValue($value) ? $value : null;
    }

    /**
     * Flattens every bucket into a single list of all stored values.
     *
     * Order is bucket index first, then insertion order within each
     * bucket; it has no relation to insertion order across the whole
     * table.
     *
     * @return list<T>
     */
    public function getAllValues(): array
    {
        $values = [];
        foreach ($this->table as $item) {
            foreach ($item as $subItem) {
                $values[] = $subItem;
            }
        }
        return $values;
    }

    /**
     * Returns the index of every bucket in the table, i.e. `0` through
     * `getCapacity() - 1`.
     *
     * This lists bucket indices, not their contents - use
     * {@see getBucket()} to read what's stored in a given bucket.
     *
     * @return list<int>
     */
    public function getBuckets(): array
    {
        $buckets = [];
        foreach ($this->table as $bucket => $items) {
            $buckets[] = $bucket;
        }
        return $buckets;
    }

    /**
     * Returns the raw contents of a single bucket.
     *
     * The keys are not guaranteed to be a contiguous 0-based list: they
     * may have gaps if a value in that bucket was previously removed via
     * {@see delete()}.
     *
     * @throws \TypeError If `$bucketIndex` is outside `0` .. `getCapacity() - 1`
     *  (an undefined bucket offset resolves to null, which violates this
     *  method's `array` return type).
     *
     * @return array<int, T>
     */
    public function getBucket(int $bucketIndex): array
    {
        return $this->table[$bucketIndex];
    }

    /**
     * Returns the current load factor: `getSize() / getCapacity()`.
     *
     * {@see insert()} triggers a resize once this exceeds 0.7.
     */
    public function getLoadFactor(): float
    {
        return $this->getSize() / $this->capacity;
    }

    /**
     * Determines whether the table holds no values (buckets may still
     * exist; only their total size is checked).
     */
    public function isEmpty(): bool
    {
        return $this->getSize() == 0;
    }
}
