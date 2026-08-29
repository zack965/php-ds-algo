<?php

namespace Zack\PhpDsAlgo\Contracts\Tree;


use IteratorAggregate;
use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTreeNode;

/**
 * Generic binary tree contract.
 *
 * @template T
 *
 * @extends IteratorAggregate<int, T>
 */
interface IBinaryTree extends ITree
{
    /**
     * Returns the root node.
     */
    public function getRoot(): ?BinaryTreeNode;
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
     * Get the diameter (longest path) of the tree.
     *
     * @return int
     */
    public function getDiameter(): int;
}
