<?php

namespace Zack\PhpDsAlgo\DataStructure\Tree;

/**
 * AVL Tree Node.
 * Extends BinaryTreeNode with height tracking for AVL balancing.
 *
 * @template T
 * @extends BinaryTreeNode<T>
 */
class AvlTreeNode extends BinaryTreeNode
{
    private int $height = 1; // New leaf starts at height 1

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }
}
