<?php


namespace Zack\PhpDsAlgo\Contracts;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T
 *
 * @extends IteratorAggregate<int, T>
 */
interface IHashTable extends IteratorAggregate, Countable
{
    /**
     * @return Traversable<int, T> Every stored value, in the same order as
     *  {@see getAllValues()}.
     */
    public function getIterator(): Traversable;

    /**
     * Alias for {@see getSize()}, wired to PHP's `count()`.
     */
    public function count(): int;
    /**
     * @param T $value
     */
    public function insert(mixed $value): void;

    /**
     * @param T $value
     */
    public function delete(mixed $value): bool;

    /**
     * @param T $oldValue
     * @param T $newValue
     */
    public function update(mixed $oldValue, mixed $newValue): bool;

    /**
     * @param T $value
     */
    public function hasValue(mixed $value): bool;

    /**
     * @param T $value
     *
     * @return array{bucketIndex: int, itemIndex: int}|false
     */
    public function getValuePosition(mixed $value): bool|array;

    /**
     * @param T $value
     *
     * @return T|null
     */
    public function getValue(mixed $value): mixed;

    /**
     * @return list<T>
     */
    public function getAllValues(): array;

    /**
     * @return list<T>
     */
    public function getBucket(int $bucketIndex): array;

    /**
     * @return list<int>
     */
    public function getBuckets(): array;

    public function getSize(): int;

    public function getCapacity(): int;

    public function getLoadFactor(): float;

    public function isEmpty(): bool;

    public function clear(): void;

    public function reset(): void;

    /**
     * Doubles the capacity and re-buckets every existing value against it.
     *
     * Called automatically once the load factor exceeds 0.7; also callable
     * directly to grow the table pre-emptively.
     */
    public function resize(): void;
}
