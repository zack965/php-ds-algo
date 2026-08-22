<?php


namespace Zack\PhpDsAlgo\Contracts;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template K
 * @template V
 *
 * @extends IteratorAggregate<K, V>
 */
interface IHashMap extends IteratorAggregate, Countable
{
    /**
     * @return Traversable<K, V> Every key-value pair, keyed by K (not
     *  restricted to int|string the way a native PHP array's keys are).
     */
    public function getIterator(): Traversable;

    /**
     * Alias for {@see getSize()}, wired to PHP's `count()`.
     */
    public function count(): int;
    /**
     * Associates a key with a value.
     *
     * If the key already exists, its associated value is replaced.
     *
     * @param K $key
     * @param V $value
     */
    public function put(mixed $key, mixed $value): void;

    /**
     * Removes the entry associated with the given key.
     *
     * @param K $key
     *
     * @return bool True if the entry was removed, false if the key does not exist.
     */
    public function delete(mixed $key): bool;

    /**
     * Updates the value associated with an existing key.
     *
     * @param K $key
     * @param V $value
     *
     * @return bool True if the key exists and was updated, false otherwise.
     */
    public function update(mixed $key, mixed $value): bool;

    /**
     * Determines whether the given key exists in the hash map.
     *
     * @param K $key
     */
    public function hasKey(mixed $key): bool;

    /**
     * Determines whether the given value exists in the hash map.
     *
     * @param V $value
     */
    public function hasValue(mixed $value): bool;

    /**
     * Returns the value associated with the given key.
     *
     * @param K $key
     *
     * @return V|null
     */
    public function get(mixed $key): mixed;

    /**
     * Returns the position of the entry associated with the given key.
     *
     * @param K $key
     *
     * @return array{bucketIndex: int, itemIndex: int}|false
     */
    public function getKeyPosition(mixed $key): bool|array;

    /**
     * Returns the position of the first entry holding the given value.
     *
     * Unlike {@see getKeyPosition()}, this scans every bucket (values
     * aren't part of the hash, only keys are) and returns the first match
     * in bucket order.
     *
     * @param V $value
     *
     * @return array{bucketIndex: int, itemIndex: int}|false
     */
    public function getValuePosition(mixed $value): bool|array;

    /**
     * Returns all key-value entries in the hash map.
     *
     * @return list<HashMapNode<K, V>>
     */
    public function getAllEntries(): array;

    /**
     * Returns all keys in the hash map.
     *
     * @return list<K>
     */
    public function getAllKeys(): array;

    /**
     * Returns all values in the hash map.
     *
     * @return list<V>
     */
    public function getAllValues(): array;

    /**
     * Returns the number of key-value entries in the hash map.
     */
    public function getSize(): int;

    /**
     * Returns the number of buckets in the hash map.
     */
    public function getCapacity(): int;

    /**
     * Returns the ratio of stored entries to available buckets.
     */
    public function getLoadFactor(): float;

    /**
     * Determines whether the hash map contains no entries.
     */
    public function isEmpty(): bool;

    /**
     * Removes all entries from the hash map while preserving its capacity.
     */
    public function clear(): void;

    /**
     * Resets the hash map to its initial state.
     *
     * This removes all entries and restores the initial capacity.
     */
    public function reset(): void;

    /**
     * Doubles the capacity and re-buckets every existing entry against it.
     *
     * Called automatically once the load factor exceeds 0.7; also callable
     * directly to grow the map pre-emptively.
     */
    public function resize(): void;
}
