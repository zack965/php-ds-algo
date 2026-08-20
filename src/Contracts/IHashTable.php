<?php


namespace Zack\PhpDsAlgo\Contracts;

/**
 * @template T
 */
interface IHashTable
{
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
}
