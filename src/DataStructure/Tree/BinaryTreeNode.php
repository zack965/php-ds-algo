<?php

namespace Zack\PhpDsAlgo\DataStructure\Tree;


/**
 * Generic binary tree node.
 *
 * @template T
 */
class BinaryTreeNode
{
    /**
     * @var T
     */
    private mixed $value;

    /**
     * @var self<T>|null
     */
    private ?self $left = null;

    /**
     * @var self<T>|null
     */
    private ?self $right = null;

    /**
     * @param T $value
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Returns the value stored in the node.
     *
     * @return T
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Sets the value stored in the node.
     *
     * @param T $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * Returns the left child.
     *
     * @return self<T>|null
     */
    public function getLeft(): ?self
    {
        return $this->left;
    }

    /**
     * Sets the left child.
     *
     * @param self<T>|null $left
     */
    public function setLeft(?self $left): void
    {
        $this->left = $left;
    }

    /**
     * Returns the right child.
     *
     * @return self<T>|null
     */
    public function getRight(): ?self
    {
        return $this->right;
    }

    /**
     * Sets the right child.
     *
     * @param self<T>|null $right
     */
    public function setRight(?self $right): void
    {
        $this->right = $right;
    }
}
