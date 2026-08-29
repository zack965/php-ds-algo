<?php

namespace Zack\PhpDsAlgo\Contracts\Tree;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Generic tree contract.
 *
 * @template T
 *
 * @extends IteratorAggregate<int, T>
 */
interface ITree extends Countable, IteratorAggregate
{


    /**
     * Determines whether the tree is empty.
     */
    public function isEmpty(): bool;

    /**
     * Removes all nodes from the tree.
     */
    public function clear(): void;

    /**
     * Returns the height of the tree.
     */
    public function getHeight(): int;

    /**
     * Determines whether the tree contains the given value.
     *
     * @param T $value
     */
    public function contains(mixed $value): bool;

    /**
     * Returns the values using level-order traversal.
     *
     * @return list<T>
     */
    public function levelOrder(): array;

    /**
     * Converts the tree to an array.
     *
     * @return list<T>
     */
    public function toArray(): array;

    /**
     * Returns an iterator over the tree values.
     *
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable;

    /**
     * Returns the number of nodes in the tree.
     */
    public function count(): int;
}
