<?php


namespace Zack\PhpDsAlgo\DataStructure\Tree;

use Generator;
use Traversable;
use Zack\PhpDsAlgo\Contracts\Tree\IAVLTree;

/**
 * AVL Tree implementation.
 * A self-balancing binary search tree where the height difference
 * between left and right subtrees (balance factor) is at most 1.
 *
 * @template T
 * @implements IAVLTree<T>
 */
class AVLTree implements IAVLTree
{
    /** @var AvlTreeNode<T>|null */
    private ?AvlTreeNode $root = null;
    private int $size = 0;

    // ==================== ITree Implementation ====================
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    public function clear(): void
    {
        $this->root = null;
        $this->size = 0;
    }
    public function getHeight(): int
    {
        return $this->getNodeHeight($this->root);
    }

    public function contains(mixed $value): bool
    {
        return $this->search($value) !== null;
    }
    public function levelOrder(): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        $result = [];
        $queue = [$this->root];

        while (!empty($queue)) {
            /** @var AvlTreeNode<T> $node */
            $node = array_shift($queue);
            $result[] = $node->getValue();

            if ($node->getLeft() !== null) {
                $queue[] = $node->getLeft();
            }
            if ($node->getRight() !== null) {
                $queue[] = $node->getRight();
            }
        }

        return $result;
    }

    public function toArray(): array
    {
        return $this->levelOrder();
    }

    public function getIterator(): Traversable
    {
        return $this->inOrderTraversal($this->root);
    }
    private function inOrderTraversal(?AvlTreeNode $node): Generator
    {
        if ($node === null) {
            return;
        }

        yield from $this->inOrderTraversal($node->getLeft());
        yield $node->getValue();
        yield from $this->inOrderTraversal($node->getRight());
    }

    public function count(): int
    {
        return $this->size;
    }
    // ==================== IAVLTree Implementation ====================
    /**
     * Helper method to safely get height from an AVL node.
     * Returns 0 if the node is null or not an AvlTreeNode.
     * @param AvlTreeNode|null $node
     * @return int
     */
    private function getNodeHeight(?AvlTreeNode $node): int
    {
        return $node ? $node->getHeight() : 0;
    }

    /**
     * Updates the node's height based on its children.
     * This is a convenience method that can be called from the tree.
     */
    public function updateHeight(AvlTreeNode $node): void
    {
        // Get left and right children, but we need to ensure they're AvlTreeNode instances
        $left = $node->getLeft();
        $right = $node->getRight();

        // Use getNodeHeight() with instanceof checks
        $leftHeight = $this->getNodeHeight($left instanceof AvlTreeNode ? $left : null);
        $rightHeight = $this->getNodeHeight($right instanceof AvlTreeNode ? $right : null);

        $node->setHeight(1 + max($leftHeight, $rightHeight));
    }
    /**
     * Calculates the balance factor of a node.
     * The balance factor determines if a node is balanced and what rotation
     * might be needed to restore AVL properties.
     * Formula: Balance = Height(Left Subtree) - Height(Right Subtree)
     * Key Points:
     *   - Positive value means left-heavy (taller on left)
     *   - Negative value means right-heavy (taller on right)  
     *   - Zero means perfectly balanced
     * Range: For a valid AVL tree, balance factor should be:
     *   - Between -1 and 1 (inclusive)
     *   - If it's > 1 or < -1, rotation is needed
     * Examples of imbalance cases:
     *   - Left-Left case: balance = 2 (left subtree is 2 levels taller)
     *   - Right-Right case: balance = -2 (right subtree is 2 levels taller)
     *   - Left-Right case: balance = 2, but left child's balance < 0
     *   - Right-Left case: balance = -2, but right child's balance > 0
     *
     * @param AvlTreeNode|null $node The node to calculate balance for
     * @return int Balance factor (left height - right height)
     *             Returns 0 for null nodes
     */
    private function getBalance(?AvlTreeNode $node): int
    {
        if (is_null($node)) {
            return 0;
        }
        $left = $this->getNodeHeight($node->getLeft());
        $right = $this->getNodeHeight($node->getRight());
        return $left - $right;
    }


    private function rotateLeft(AvlTreeNode $node): AvlTreeNode
    {
        $newRoot = $node->getRight();
        $middle = $node->getRight()->getLeft();

        $node->setRight($middle);
        $newRoot->setLeft($node);


        $this->updateHeight($node);
        $this->updateHeight($newRoot);


        return $newRoot;
    }

    private function rotateRight(AvlTreeNode $node): AvlTreeNode
    {
        $newRoot = $node->getLeft();
        $middle = $newRoot->getRight();

        $node->setLeft($middle);
        $newRoot->setRight($node);


        $this->updateHeight($node);
        $this->updateHeight($newRoot);


        return $newRoot;
    }

    private function rebalance(?AvlTreeNode $node): ?AvlTreeNode
    {
        if (is_null($node)) {
            return null;
        }
        $this->updateHeight($node);
        $balence = $this->getNodeHeight($node->getLeft()) - $this->getNodeHeight($node->getRight());
        if ($balence >= -1 && $balence <= 1) {
            return $node;
        }
        if ($balence > 1) {
            $leftBalence = $this->getNodeHeight($node->getLeft()->getLeft()) - $this->getNodeHeight($node->getLeft()->getRight());

            if ($leftBalence >= 0) {
                return $this->rotateRight($node);
            } else {
                $node->setLeft($this->rotateLeft($node->getLeft()));
                return $this->rotateRight($node);
            }
        }
        if ($balence < -1) {
            $rightBalence = $this->getNodeHeight($node->getRight()->getLeft()) - $this->getNodeHeight($node->getRight()->getRight());
            if ($rightBalence <= 0) {
                return $this->rotateLeft($node);
            } else {
                $node->setRight($this->rotateRight($node->getRight()));
                return $this->rotateLeft($node);
            }
        }

        return $node;
    }
    public function insert(mixed $value): static
    {
        return $this;
    }

    public function remove(mixed $value): static
    {
        return $this;
    }

    public function search(mixed $value): ?BinaryTreeNode
    {
        return null;
    }

    public function min(): mixed
    {
        return null;
    }

    public function max(): mixed
    {
        return null;
    }

    public function predecessor(mixed $value): mixed
    {
        return null;
    }

    public function successor(mixed $value): mixed
    {
        return null;
    }

    public function floor(mixed $value): mixed
    {
        return null;
    }

    public function findClosest(mixed $value): mixed
    {
        return null;
    }
}
