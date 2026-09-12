<?php


namespace Zack\PhpDsAlgo\Contracts\Tree;

use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTreeNode;

/**
 * @template T
 *
 * @extends ITree<T>
 */
interface IAVLTree extends ITree
{
    /**
     * Inserts a value into the AVL tree.
     * Maintains AVL balance property through rotations.
     *
     * @param T $value The value to insert
     * @return static Returns the tree instance for method chaining
     */
    public function insert(mixed $value): static;

    /**
     * Removes a value from the AVL tree.
     * Maintains AVL balance property through rotations.
     *
     * @param T $value The value to remove
     * @return static Returns the tree instance for method chaining
     */
    public function remove(mixed $value): static;

    /**
     * Searches for a value in the AVL tree.
     *
     * @param T $value The value to search for
     * @return BinaryTreeNode<T>|null The node containing the value, or null if not found
     */
    public function search(mixed $value): ?BinaryTreeNode;

    /**
     * Finds the minimum value in the AVL tree.
     *
     * @return T|null The minimum value, or null if tree is empty
     */
    public function min(): mixed;

    /**
     * Finds the maximum value in the AVL tree.
     *
     * @return T|null The maximum value, or null if tree is empty
     */
    public function max(): mixed;

    /**
     * Finds the predecessor of a given value.
     * The predecessor is the largest value less than the given value.
     *
     * @param T $value The reference value
     * @return T|null The predecessor value, or null if not found
     */
    public function predecessor(mixed $value): mixed;

    /**
     * Finds the successor of a given value.
     * The successor is the smallest value greater than the given value.
     *
     * @param T $value The reference value
     * @return T|null The successor value, or null if not found
     */
    public function successor(mixed $value): mixed;

    /**
     * Finds the floor of a given value.
     * The floor is the largest value less than or equal to the given value.
     *
     * @param T $value The reference value
     * @return T|null The floor value, or null if not found
     */
    public function floor(mixed $value): mixed;

    /**
     * Finds the closest value to the given value.
     * If two values are equally close, the smaller one is returned.
     *
     * @param T $value The reference value
     * @return T|null The closest value, or null if tree is empty
     */
    public function findClosest(mixed $value): mixed;
}
