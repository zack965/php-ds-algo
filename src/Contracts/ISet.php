<?php

namespace Zack\PhpDsAlgo\Contracts;

use Countable;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * A collection of unique values with no guaranteed iteration order.
 *
 * Membership is determined by strict comparison (===): two values are the
 * same element of the set only if they have the same type and value (for
 * objects, the same instance). `null` is never a valid element — passing
 * `null` to `add()`, `contains()`, or `remove()` is undefined by this
 * contract and implementations are free to reject it.
 *
 * `add()`, `remove()`, and `clear()` mutate the set they are called on.
 * `union()`, `intersection()`, and `difference()` are pure: they never
 * modify `$this` or `$other`, and always return a new `ISet` instance.
 *
 * @template T
 *
 * @extends IteratorAggregate<int, T>
 */
interface ISet extends Countable, IteratorAggregate
{
    /**
     * Adds a value to the set, unless an equal value is already present.
     *
     * Mutates the set in place.
     *
     * @param T $value The value to add.
     *
     * @return bool True if the value was added, false if an equal value
     *              already existed (the set is unchanged in that case).
     */
    public function add(mixed $value): bool;

    /**
     * Determines whether the set contains a value equal (===) to $value.
     *
     * @param T $value The value to look for.
     *
     * @return bool True if an equal value is present, false otherwise.
     */
    public function contains(mixed $value): bool;

    /**
     * Removes the value equal (===) to $value from the set, if present.
     *
     * Mutates the set in place. Does nothing if no equal value exists.
     *
     * @param T $value The value to remove.
     *
     * @return bool True if a value was removed, false if it did not exist
     *              (the set is unchanged in that case).
     */
    public function remove(mixed $value): bool;

    /**
     * Removes every value from the set, leaving it empty.
     *
     * Mutates the set in place.
     */
    public function clear(): void;

    /**
     * Determines whether the set contains no values.
     *
     * Equivalent to `count($set) === 0`.
     */
    public function isEmpty(): bool;

    /**
     * Returns every value in the set as a plain, 0-indexed array.
     *
     * The order of values is not guaranteed by this contract. The returned
     * array is a snapshot: mutating it does not affect the set.
     *
     * @return list<T>
     */
    public function getAll(): array;

    /**
     * Returns the number of values currently in the set.
     */
    public function count(): int;

    /**
     * Returns a new set containing every value that is in this set,
     * in $other, or in both.
     *
     * Pure: neither $this nor $other is modified.
     *
     * @param ISet<T> $other The set to union with.
     *
     * @return ISet<T> A new set: $this ∪ $other.
     */
    public function union(ISet $other): ISet;

    /**
     * Returns a new set containing only the values present in both
     * this set and $other.
     *
     * Pure: neither $this nor $other is modified.
     *
     * @param ISet<T> $other The set to intersect with.
     *
     * @return ISet<T> A new set: $this ∩ $other.
     */
    public function intersection(ISet $other): ISet;

    /**
     * Returns a new set containing the values in this set that are
     * NOT present in $other (relative complement).
     *
     * Pure: neither $this nor $other is modified. Note this is
     * directional: `$a->difference($b)` is generally not the same as
     * `$b->difference($a)`.
     *
     * @param ISet<T> $other The set of values to exclude.
     *
     * @return ISet<T> A new set: $this \ $other.
     */
    public function difference(ISet $other): ISet;

    /**
     * Determines whether every value in this set is also in $other.
     *
     * The empty set is a subset of every set, and every set is a subset
     * of itself.
     *
     * @param ISet<T> $other The set to compare against.
     *
     * @return bool True if $this ⊆ $other.
     */
    public function isSubsetOf(ISet $other): bool;

    /**
     * Determines whether every value in $other is also in this set.
     *
     * Equivalent to `$other->isSubsetOf($this)`.
     *
     * @param ISet<T> $other The set to compare against.
     *
     * @return bool True if $this ⊇ $other.
     */
    public function isSupersetOf(ISet $other): bool;

    /**
     * Determines whether this set and $other contain exactly the same
     * values, irrespective of insertion or iteration order.
     *
     * @param ISet<T> $other The set to compare against.
     *
     * @return bool True if $this and $other have the same size and every
     *              value in one is present in the other.
     */
    public function equals(ISet $other): bool;
    /**
     * Returns the value at the given iteration index.
     *
     * The index refers to the current iteration order of the set.
     * Set implementations do not guarantee a stable ordering.
     *
     * @param int $index The zero-based iteration index.
     *
     * @return T The value at the given index.
     *
     * @throws OutOfBoundsException If the index is outside the set's range.
     */
    public function get(int $index): mixed;

    /**
     * Returns the iteration index of the given value.
     *
     * The returned index refers to the current iteration order of the set.
     * Set implementations do not guarantee a stable ordering.
     *
     * @param T $value The value whose index should be found.
     *
     * @return int|false The zero-based iteration index, or false if the value
     *                   does not exist in the set.
     */
    public function indexOf(mixed $value): int|false;
    /**
     * Replaces an existing value with a new value.
     *
     * The replacement is performed only if the old value exists and the new
     * value does not already exist in the set. The set remains unchanged if
     * either condition is not satisfied.
     *
     * @param T $oldValue The value to replace.
     * @param T $newValue The replacement value.
     *
     * @return bool True if the value was successfully replaced, false if
     *              $oldValue does not exist or $newValue already exists.
     */
    public function update(mixed $oldValue, mixed $newValue): bool;
}
