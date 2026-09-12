<?php

namespace Zack\PhpDsAlgo\DataStructure\Tree;

use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme;
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;
use Zack\PhpDsAlgo\Contracts\Tree\IBinaryTree;
use Zack\PhpDsAlgo\DataStructure\Queue\Queue;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

/**
 * Generic binary tree.
 *
 * @template T
 *
 * @implements IBinaryTree<T>
 * @implements IteratorAggregate<int, T>
 */
class BinaryTree extends AbstractTree implements IBinaryTree
{
    private int $maxDiameter = 0;

    /**
     * Returns the values via pre-order traversal (node, left, right).
     *
     * @return list<T>
     */
    public function preOrder(): array
    {
        $results = [];
        $this->traversePreOrder($this->root, $results);
        return $results;
    }
    /**
     * @param list<T> &$results Appended to in place, in pre-order.
     */
    private function traversePreOrder(?BinaryTreeNode $node, array &$results)
    {
        if (is_null($node)) {
            return;
        }
        $results[] = $node->getValue();
        $this->traversePreOrder($node->getLeft(), $results);
        $this->traversePreOrder($node->getRight(), $results);
    }

    /**
     * Returns the values via in-order traversal (left, node, right).
     *
     * @return list<T>
     */
    public function inOrder(): array
    {
        $results = [];
        $this->traverseInOrder($this->root, $results);
        return $results;
    }

    /**
     * @param list<T> &$results Appended to in place, in in-order.
     */
    private function traverseInOrder(?BinaryTreeNode $node, array &$results)
    {
        if (is_null($node)) {
            return;
        }
        $this->traverseInOrder($node->getLeft(), $results);
        $results[] = $node->getValue();
        $this->traverseInOrder($node->getRight(), $results);
    }
    /**
     * Returns the values via post-order traversal (left, right, node).
     *
     * @return list<T>
     */
    public function postOrder(): array
    {
        $results = [];
        $this->traversePostOrder($this->root, $results);
        return $results;
    }
    /**
     * @param list<T> &$results Appended to in place, in post-order.
     */
    private function traversePostOrder(?BinaryTreeNode $node, array &$results)
    {
        if (is_null($node)) {
            return;
        }
        $this->traversePostOrder($node->getLeft(), $results);
        $this->traversePostOrder($node->getRight(), $results);
        $results[] = $node->getValue();
    }


    /**
     * Determines whether every node has either 0 or 2 children (never
     * exactly 1). Vacuously `true` for an empty tree.
     */
    public function isFull(): bool
    {
        return  $this->traverse($this->root);
    }
    /**
     * Recursively checks {@see isNodeFull()} for $node and every descendant.
     */
    private function traverse(?BinaryTreeNode $node): bool
    {
        if (is_null($node)) {
            return true;
        }
        if (!$this->isNodeFull($node)) {
            return false;  // Current node violates the rule
        }
        $leftResult = $this->traverse($node->getLeft());
        $rightResult = $this->traverse($node->getRight());

        return $leftResult && $rightResult;
    }
    /**
     * Determines whether $node itself (not its descendants) has either 0 or
     * 2 children.
     */
    private function isNodeFull(?BinaryTreeNode $node): bool
    {
        if (is_null($node)) {
            return true;
        }
        if (is_null($node->getLeft()) && is_null($node->getRight())) {
            return true;
        }
        if (!is_null($node->getLeft()) && !is_null($node->getRight())) {
            return true;
        }
        return false;
    }

    /**
     * Determines whether every level is fully filled except possibly the
     * last, which must be filled left to right with no gaps. Checked via a
     * breadth-first scan: once a node with a missing child is dequeued, no
     * node dequeued afterward may have any child at all.
     */
    public function isComplete(): bool
    {
        $queue = new Queue();
        if (is_null($this->root)) {
            return true;
        }
        $queue->enqueue($this->root);
        $hasNullChild = false;
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $node */
            $node = $queue->dequeue();
            $leftChild = $node->getLeft();
            $rightChild = $node->getRight();
            if ($leftChild  && $hasNullChild) {
                return false;
            }
            if ($rightChild && $hasNullChild) {  // ← You have this right?
                return false;
            }
            if ($leftChild) {
                $queue->enqueue($leftChild);
            } else {
                $hasNullChild = true;
            }
            if ($rightChild) {
                $queue->enqueue($rightChild);
            } else {
                $hasNullChild = true;
            }
        }
        return true;
    }

    /**
     * Determines whether every internal node has exactly 2 children and
     * every leaf sits at the same depth — equivalent to the tree holding
     * exactly `2^(height+1) - 1` nodes. Vacuously `true` for an empty tree.
     */
    public function isPerfect(): bool
    {
        if (is_null($this->root)) {
            return true;
        }
        $height = $this->getHeight();
        $expectedSize =  (int) pow(2, $height + 1) - 1;
        return $this->size == $expectedSize;
    }


    /**
     * Determines whether, for every node, its two subtrees' heights differ
     * by at most 1. `true` for an empty tree.
     */
    public function isBalanced(): bool
    {
        if (is_null($this->root)) {
            return true;
        }
        return $this->checkBalance($this->root) !== -1;
    }
    /**
     * Returns the height (in edges) of the subtree rooted at $node — same
     * convention as {@see AbstractTree::getNodeHeight()}, except an empty
     * subtree's height is `0` here (not `-1`), which frees `-1` to serve
     * purely as the "an imbalance was found downstream" signal that
     * short-circuits every ancestor call straight back up to
     * {@see isBalanced()} without being confused for a real height.
     */
    private function checkBalance(?BinaryTreeNode $node): int
    {
        if (is_null($node)) {
            return 0;
        }
        $leftHeight = $this->checkBalance($node->getLeft());
        if ($leftHeight === -1) {
            return -1;
        }
        $rightHeight = $this->checkBalance($node->getRight());
        if ($rightHeight === -1) {
            return -1;
        }
        if (abs($leftHeight - $rightHeight) > 1) {
            return -1;
        }
        return 1 + max($leftHeight, $rightHeight);
    }


    /**
     * Inserts $value at the first free spot in breadth-first (level) order,
     * so the tree fills left to right, level by level.
     */
    public function insert(mixed $value): static
    {
        $newNode = new BinaryTreeNode($value);
        if (is_null($this->root)) {
            $this->root = $newNode;
            $this->size++;
            return $this;
        }
        $queue = new Queue();
        $queue->enqueue($this->root);
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $node */
            $node = $queue->dequeue();
            $leftChild = $node->getLeft();
            $rightChild = $node->getRight();
            if (is_null($leftChild)) {
                $node->setLeft($newNode);
                $this->size++;
                return $this;
            } else {
                $queue->enqueue($leftChild);
            }
            if (is_null($rightChild)) {
                $node->setRight($newNode);
                $this->size++;
                return $this;
            } else {
                $queue->enqueue($rightChild);
            }
        }

        return $this;
    }
    /**
     * Removes the first occurrence of a value from the tree.
     * This uses the standard binary tree removal algorithm:
     * 1. Find the target node and the deepest/rightmost node
     * 2. Replace target's value with deepest node's value
     * 3. Delete the deepest node
     * @param T $value The value to remove
     * @return static The current tree instance for method chaining
     */
    public function remove(mixed $value): static
    {
        if (is_null($this->root)) {
            return $this;
        }
        if (is_null($this->root->getLeft()) && is_null($this->root->getRight())) {
            if ($this->root->getValue() === $value) {
                $this->root = null;
                $this->size = 0;
            }
            return $this;
        }

        $queue = new Queue();
        $targetNode = null;
        $deepestNode = null;
        /** @var BinaryTreeNode<T>|null $parentOfDeepest */        $parentOfDeepest = null;
        $isDeepestLeft = false;

        $queue->enqueue($this->root);
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $currentNode */
            $currentNode = $queue->dequeue();
            $deepestNode = $currentNode;
            if ($currentNode->getValue() === $value) {
                $targetNode = $currentNode;
            }
            $leftChild = $currentNode->getLeft();
            $rightChild = $currentNode->getRight();
            if ($leftChild) {
                $parentOfDeepest = $currentNode;
                $isDeepestLeft = true;
                $queue->enqueue($leftChild);
            }
            if ($rightChild) {
                $parentOfDeepest = $currentNode;
                $isDeepestLeft = false;
                $queue->enqueue($rightChild);
            }
        }
        if (is_null($targetNode)) {
            return $this;
        }
        if (is_null($parentOfDeepest)) {
            return $this;
        }
        if ($targetNode === $deepestNode) {
            if ($isDeepestLeft) {
                $parentOfDeepest->setLeft(null);
            }
            if (!$isDeepestLeft) {
                $parentOfDeepest->setRight(null);
            }
            $this->size--;
            return $this;
        }
        $targetNode->setValue($deepestNode->getValue());
        if ($isDeepestLeft) {
            $parentOfDeepest->setLeft(null);
        }
        if (!$isDeepestLeft) {
            $parentOfDeepest->setRight(null);
        }
        $this->size--;
        return $this;
    }

    /**
     * Searches for the first node holding $value via breadth-first scan (no
     * ordering property to exploit, unlike {@see BinarySearchTree::search()}).
     */
    public function search(mixed $value): ?BinaryTreeNode
    {
        if (is_null($this->root)) {
            return null;
        }

        $queue = new Queue();
        $queue->enqueue($this->root);
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $node */
            $node = $queue->dequeue();
            $leftChild = $node->getLeft();
            $rightChild = $node->getRight();

            if ($node->getValue() === $value) {
                return $node;
            }
            if ($leftChild) {
                $queue->enqueue($leftChild);
            }
            if ($rightChild) {
                $queue->enqueue($rightChild);
            }
        }

        return null;
    }




    /**
     * Returns the diameter: the length (in edges) of the longest path
     * between any two nodes in the tree. `0` for an empty or single-node
     * tree (no edges exist).
     */
    public function getDiameter(): int
    {
        $this->maxDiameter = 0;
        $this->calculateHeight($this->root);
        return $this->maxDiameter;
    }

    /**
     * Returns the height (in edges) of the subtree rooted at $node — same
     * convention as {@see AbstractTree::getNodeHeight()} (`-1` empty, `0` a
     * leaf) — and, as a side effect, updates `$maxDiameter` with the
     * longest path passing through $node: reaching a leaf on the left costs
     * `leftHeight + 1` edges from $node, and `rightHeight + 1` on the
     * right, so the path through $node spans `leftHeight + rightHeight + 2`
     * edges in total.
     */
    private function calculateHeight(?BinaryTreeNode $node): int
    {
        if (is_null($node)) {
            return -1;
        }

        $leftHeight = $this->calculateHeight($node->getLeft());
        $rightHeight = $this->calculateHeight($node->getRight());

        $currentDiameter = $leftHeight + $rightHeight + 2;
        $this->maxDiameter = max($this->maxDiameter, $currentDiameter);

        return 1 + max($leftHeight, $rightHeight);
    }
}
