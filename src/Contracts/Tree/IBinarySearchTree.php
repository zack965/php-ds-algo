<?php

namespace Zack\PhpDsAlgo\Contracts\Tree;

use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTreeNode;

/**
 * Generic binary search tree contract.
 *
 * All values must be comparable using standard PHP comparison operators (<, >, ==).
 *
 * @template T
 * @extends IBinaryTree<T>
 */
interface IBinarySearchTree extends ITree
{
    /**
     * Returns the node containing the minimum value.
     * O(log n) average, O(n) worst case.
     *
     * @return BinaryTreeNode<T>|null
     */
    public function min(): ?BinaryTreeNode;

    /**
     * Returns the node containing the maximum value.
     * O(log n) average, O(n) worst case.
     *
     * @return BinaryTreeNode<T>|null
     */
    public function max(): ?BinaryTreeNode;

    /**
     * Returns the predecessor of a value (largest value < given value).
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return BinaryTreeNode<T>|null
     */
    public function predecessor(mixed $value): ?BinaryTreeNode;

    /**
     * Returns the successor of a value (smallest value > given value).
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return BinaryTreeNode<T>|null
     */
    public function successor(mixed $value): ?BinaryTreeNode;

    /**
     * Returns the floor (largest value ≤ target).
     * O(log n) average, O(n) worst case.
     *
     * @param T $target
     * @return T|null
     */
    public function floor(mixed $target): mixed;

    /**
     * Returns the ceiling (smallest value ≥ target).
     * O(log n) average, O(n) worst case.
     *
     * @param T $target
     * @return T|null
     */
    public function ceiling(mixed $target): mixed;

    /**
     * Finds the closest value to the target.
     * O(log n) average, O(n) worst case.
     *
     * @param T $target
     * @return T|null Returns null if tree is empty
     */
    public function findClosest(mixed $target): mixed;

    /**
     * Returns all values in the inclusive range [low, high] in sorted order.
     * O(n) time, O(m) space where m is the number of values in range.
     *
     * @param T $low
     * @param T $high
     * @return list<T>
     */
    public function rangeSearch(mixed $low, mixed $high): array;

    /**
     * Counts nodes with values in the inclusive range [low, high].
     * O(n) time.
     *
     * @param T $low
     * @param T $high
     * @return int
     */
    public function countInRange(mixed $low, mixed $high): int;

    /**
     * Returns the kth smallest element (1-indexed).
     * O(n) time for unsorted tree, can be O(log n) if augmented with subtree sizes.
     *
     * @param int $k
     * @return T|null Returns null if k is out of bounds
     */
    public function kthSmallest(int $k): mixed;

    /**
     * Returns the kth largest element (1-indexed).
     * O(n) time for unsorted tree, can be O(log n) if augmented with subtree sizes.
     *
     * @param int $k
     * @return T|null Returns null if k is out of bounds
     */
    public function kthLargest(int $k): mixed;

    /**
     * Returns the lowest common ancestor of two values.
     * O(log n) average, O(n) worst case.
     *
     * @param T $a
     * @param T $b
     * @return BinaryTreeNode<T>|null Returns null if either value not found
     */
    public function lowestCommonAncestor(mixed $a, mixed $b): ?BinaryTreeNode;

    /**
     * Validates that the tree satisfies BST invariants.
     * For every node: left subtree values < node value < right subtree values.
     * O(n) time.
     */
    public function isValid(): bool;

    /**
     * Balances the tree to maintain optimal O(log n) operations.
     * Implements Day-Stout-Warren (DSW) algorithm or AVL rotation.
     * O(n) time.
     *
     * @return static
     */
    public function balance(): static;

    /**
     * Creates a BST from an array of values.
     * Duplicate values are ignored.
     *
     * @param list<T> $values
     * @return static
     */
    public static function fromArray(array $values): static;

    /**
     * Inserts a value into the BST maintaining BST order.
     * Overrides IBinaryTree::insert to ensure BST property.
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return static
     */
    public function insert(mixed $value): static;

    /**
     * Removes a value from the BST maintaining BST order.
     * Overrides IBinaryTree::remove to ensure BST property.
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return static
     */
    public function remove(mixed $value): static;

    /**
     * Searches for a node by value using BST properties.
     * Overrides IBinaryTree::search for optimized O(log n) search.
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return BinaryTreeNode<T>|null
     */
    public function search(mixed $value): ?BinaryTreeNode;
}
