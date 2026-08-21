<?php


namespace Zack\PhpDsAlgo\DataStructure\Set;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;
use Zack\PhpDsAlgo\Contracts\ISet;

/**
 * An array-backed implementation of {@see ISet}.
 *
 * Values are stored in an internal list and de-duplicated using strict
 * comparison (===) via {@see GeneralArrayAlgorithms}. Because membership
 * checks and removals scan the internal array linearly, `add()`,
 * `contains()`, and `remove()` are all O(n) in the number of elements —
 * this implementation favors simplicity over lookup speed.
 *
 * `null` is not a supported element: passing it to `add()`, `contains()`,
 * or `remove()` bubbles up an `InvalidArgumentException` from
 * {@see GeneralArrayAlgorithms::contains()}.
 *
 * @template T
 *
 * @extends IteratorAggregate<int, T>
 */
class Set implements ISet
{
    /**
     * Values contained in the set. Always de-duplicated (strict comparison)
     * and re-indexed as a 0-based list.
     *
     * @var list<T>
     */
    private array $data = [];


    /**
     * Creates a set from an initial list of values.
     *
     * Values are added one by one via {@see self::add()}, so duplicates in
     * $data (by strict comparison) are silently collapsed to a single
     * element and iteration order follows first occurrence.
     *
     * @param list<T> $data Initial values. Defaults to an empty set.
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $value) {
            $this->add($value);
        }
    }


    /**
     * @return Traversable<int, T> An iterator over the set's values, in
     *                             insertion order.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Adds a value to the set, unless an equal (===) value is already present.
     *
     * @param T $value
     *
     * @return bool True if the value was added, false if it already existed.
     */
    public function add(mixed $value): bool
    {
        if (GeneralArrayAlgorithms::contains($this->data, $value)) {
            return false;
        }
        $this->data[] = $value;
        return true;
    }

    /**
     * Determines whether the set contains a value equal (===) to $value.
     *
     * @param T $value
     */
    public function contains(mixed $value): bool
    {
        if (GeneralArrayAlgorithms::contains($this->data, $value)) {
            return true;
        }
        return false;
    }

    /**
     * Removes the value equal (===) to $value from the set, if present.
     *
     * @param T $value
     *
     * @return bool True if a value was removed, false if it did not exist.
     */
    public function remove(mixed $value): bool
    {
        if (!GeneralArrayAlgorithms::contains($this->data, $value)) {
            return false;
        }
        $this->data = GeneralArrayAlgorithms::remove($this->data, $value);
        return true;
    }

    /**
     * Removes every value from the set, leaving it empty.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Determines whether the set contains no values.
     */
    public function isEmpty(): bool
    {
        return $this->count() == 0;
    }

    /**
     * Returns every value in the set as a plain, 0-indexed array, in
     * insertion order. The returned array is a snapshot — mutating it does
     * not affect the set.
     *
     * @return list<T>
     */
    public function getAll(): array
    {
        return array_values($this->data);
    }

    /**
     * Returns the number of values currently in the set.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Returns a new set containing every value that is in this set, in
     * $other, or in both ($this ∪ $other). Values from $this come first,
     * followed by values from $other that weren't already present.
     *
     * Pure: neither $this nor $other is modified.
     *
     * @param ISet<T> $other
     *
     * @return ISet<T>
     */
    public function union(ISet $other): ISet
    {
        $result = new self($this->getAll());

        foreach ($other->getAll() as $value) {
            $result->add($value);
        }

        return $result;
    }

    /**
     * Returns a new set containing only the values present in both this
     * set and $other ($this ∩ $other), in this set's iteration order.
     *
     * Pure: neither $this nor $other is modified.
     *
     * @param ISet<T> $other
     *
     * @return ISet<T>
     */
    public function intersection(ISet $other): ISet
    {
        $values = [];
        $otherValues = $other->getAll();

        foreach ($this->getAll() as $value) {
            if (GeneralArrayAlgorithms::contains($otherValues, $value)) {
                $values[] = $value;
            }
        }

        return new self($values);
    }

    /**
     * Returns a new set containing the values in this set that are NOT
     * present in $other ($this \ $other), in this set's iteration order.
     *
     * Pure: neither $this nor $other is modified. Directional — generally
     * `$a->difference($b) !== $b->difference($a)`.
     *
     * @param ISet<T> $other
     *
     * @return ISet<T>
     */
    public function difference(ISet $other): ISet
    {
        $values = [];
        $otherValues = $other->getAll();
        foreach ($this->getAll() as $value) {
            if (!GeneralArrayAlgorithms::contains($otherValues, $value)) {
                $values[] = $value;
            }
        }

        return new self($values);
    }

    /**
     * Determines whether every value in this set is also in $other
     * ($this ⊆ $other). The empty set is a subset of every set.
     *
     * @param ISet<T> $other
     */
    public function isSubsetOf(ISet $other): bool
    {
        $thisValues = $this->getAll();
        $otherValues = $other->getAll();

        foreach ($thisValues as $value) {
            if (!GeneralArrayAlgorithms::contains($otherValues, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines whether every value in $other is also in this set
     * ($this ⊇ $other). Equivalent to `$other->isSubsetOf($this)`.
     *
     * @param ISet<T> $other
     */
    public function isSupersetOf(ISet $other): bool
    {
        $thisValues = $this->getAll();
        $otherValues = $other->getAll();

        foreach ($otherValues as $value) {
            if (!GeneralArrayAlgorithms::contains($thisValues, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines whether this set and $other contain exactly the same
     * values, irrespective of insertion or iteration order. Compares size
     * first (O(1)), then checks that every value in $this is present in
     * $other.
     *
     * @param ISet<T> $other
     */
    public function equals(ISet $other): bool
    {
        if ($this->count() !== $other->count()) {
            return false;
        }

        foreach ($this->data as $value) {
            if (!GeneralArrayAlgorithms::contains($other->getAll(), $value)) {
                return false;
            }
        }

        return true;
    }
}
