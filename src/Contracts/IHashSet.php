<?php


namespace Zack\PhpDsAlgo\Contracts;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @template T
 *
 * @extends IteratorAggregate<int, T>
 */
interface IHashSet extends IteratorAggregate, Countable
{
    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable;

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
     *
     * @return bool
     *
     * @throws InvalidArgumentException If either value cannot be used as a hash table value.
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
     * @return list<T>
     */
    public function getAllValues(): array;

    public function getSize(): int;

    public function getCapacity(): int;

    public function getLoadFactor(): float;

    public function isEmpty(): bool;

    public function clear(): void;

    public function reset(): void;

    /**
     * Doubles the capacity and re-buckets every existing value.
     */
    public function resize(): void;
}
