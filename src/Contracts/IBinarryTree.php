<?php

namespace Zack\PhpDsAlgo\Contracts;

use Countable;
use IteratorAggregate;
use Traversable;
use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTreeNode;

/**
 * Generic binary tree contract.
 *
 * @template T
 *
 * @extends IteratorAggregate<int, T>
 */
interface IBinaryTree extends Countable, IteratorAggregate
{
    /**
     * Returns the root node.
     *
     * @return BinaryTreeNode<T>|null
     */
    public function getRoot(): ?BinaryTreeNode;



    /**
     * Determines whether the tree is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Removes all nodes from the tree.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Returns the height of the tree.
     *
     * @return int
     */
    public function getHeight(): int;

    /**
     * Determines whether the tree contains the given value.
     *
     * @param T $value
     *
     * @return bool
     */
    public function contains(mixed $value): bool;

    /**
     * Returns the values using preorder traversal.
     *
     * @return list<T>
     */
    public function preOrder(): array;

    /**
     * Returns the values using inorder traversal.
     *
     * @return list<T>
     */
    public function inOrder(): array;

    /**
     * Returns the values using postorder traversal.
     *
     * @return list<T>
     */
    public function postOrder(): array;

    /**
     * Returns the values using level-order traversal.
     *
     * @return list<T>
     */
    public function levelOrder(): array;

    /**
     * Determines whether the tree is full.
     *
     * @return bool
     */
    public function isFull(): bool;

    /**
     * Determines whether the tree is complete.
     *
     * @return bool
     */
    public function isComplete(): bool;

    /**
     * Determines whether the tree is perfect.
     *
     * @return bool
     */
    public function isPerfect(): bool;

    /**
     * Determines whether the tree is balanced.
     *
     * @return bool
     */
    public function isBalanced(): bool;

    /**
     * Returns an iterator over the tree values.
     *
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable;

    /**
     * Returns the number of nodes in the tree.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Insert a value at the next available position (heap-like insertion).
     *
     * @param T $value
     * @return static
     */
    public function insert(mixed $value): static;

    /**
     * Remove a node by value and reorganize.
     *
     * @param T $value
     * @return static
     */
    public function remove(mixed $value): static;

    /**
     * Search for a node by value.
     *
     * @param T $value
     * @return BinaryTreeNode<T>|null
     */
    public function search(mixed $value): ?BinaryTreeNode;

    /**
     * Convert tree to array representation (level-order).
     *
     * @return list<T>
     */
    public function toArray(): array;

    /**
     * Get the diameter (longest path) of the tree.
     *
     * @return int
     */
    public function getDiameter(): int;
}
