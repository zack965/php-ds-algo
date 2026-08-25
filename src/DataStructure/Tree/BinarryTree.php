<?php

namespace Zack\PhpDsAlgo\DataStructure\Tree;

use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme;
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;
use Zack\PhpDsAlgo\Contracts\IBinaryTree;
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
class BinaryTree implements IBinaryTree
{
    private int $maxDiameter = 0;

    /**
     * @var BinaryTreeNode<T>|null
     */
    private ?BinaryTreeNode $root = null;
    private int $size = 0;
    /**
     * @param list<T> $values
     */
    public function __construct(array $values = [])
    {
        if (empty($values)) {
            return;
        }
        $nodes = $this->buildNodes($values);
        $this->connectNodes($values, $nodes);

        $this->size = count($values) - 1;
    }
    /**
     * @param list<T> $values
     * @return list<BinaryTreeNode<T>>
     */
    private function buildNodes(array $values): array
    {
        $nodes = [];
        foreach ($values as $value) {
            $nodes[] = new BinaryTreeNode($value);
        }
        return $nodes;
    }
    /**
     * @param list<T> $values
     * @param list<BinaryTreeNode<T>> $nodes
     */
    private function connectNodes(array $values, array $nodes)
    {
        for ($i = 0; $i < count($values); $i++) {
            $currentNode = $nodes[$i];

            if ($i == 0) {
                $this->root = $currentNode;
            } else {
                $parentIndex = $this->getParentIndex($i);
                $parentNode = $nodes[$parentIndex];
                if (AlgorythmesGlobalHelpers::isOdd($i)) {
                    // left
                    $parentNode->setLeft($currentNode);
                }
                if (AlgorythmesGlobalHelpers::isEven($i)) {
                    // right
                    $parentNode->setRight($currentNode);
                }
            }
        }
    }

    private function getLeftChildIndex(int $index): int
    {
        return 2 * $index + 1;
    }

    private function getRightChildIndex(int $index): int
    {
        return 2 * $index + 2;
    }

    private function getParentIndex(int $index): int
    {
        return (int) floor(($index - 1) / 2);
    }
    public function getRoot(): ?BinaryTreeNode
    {
        return $this->root;
    }




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
    private function getNodeHeight(?BinaryTreeNode $node)
    {
        if (is_null($node)) {
            return -1;
        }
        $leftNodeHeight = $this->getNodeHeight($node->getLeft());
        $rightNodeHeight = $this->getNodeHeight($node->getRight());
        return 1 + max($leftNodeHeight, $rightNodeHeight);
    }

    /**
     * @param T $value
     */
    public function contains(mixed $value): bool
    {
        $results = $this->inOrder();
        return GeneralArrayAlgorithms::contains($results, $value);
    }

    /**
     * @return list<T>
     */
    public function preOrder(): array
    {
        $results = [];
        $this->traversePreOrder($this->root, $results);
        return $results;
    }
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
     * @return list<T>
     */
    public function inOrder(): array
    {
        $results = [];
        $this->traverseInOrder($this->root, $results);
        return $results;
    }

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
     * @return list<T>
     */
    public function postOrder(): array
    {
        $results = [];
        $this->traversePostOrder($this->root, $results);
        return $results;
    }
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
     * @return list<T>
     */
    public function levelOrder(): array
    {
        if (is_null($this->root)) {
            return [];
        }
        $queue = new Queue();
        $queue->enqueue($this->root);
        $results = [];
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $node */
            $node = $queue->dequeue();
            $results[] = $node->getValue();
            if ($node->getLeft()) {
                $queue->enqueue($node->getLeft());
            }
            if ($node->getRight()) {
                $queue->enqueue($node->getRight());
            }
        }
        return $results;
    }

    public function isFull(): bool
    {
        return  $this->traverse($this->root);
    }
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

    public function isPerfect(): bool
    {
        if (is_null($this->root)) {
            return true;
        }
        $height = $this->getHeight();
        $expectedSize =  (int) pow(2, $height + 1) - 1;
        return $this->size == $expectedSize;
    }

    private function isLeaf(?BinaryTreeNode $node): bool
    {
        return !is_null($node)
            && is_null($node->getLeft())
            && is_null($node->getRight());
    }
    public function isBalanced(): bool
    {
        return $this->checkBalance($this->root) !== -1;
    }
    private function checkBalance(?BinaryTreeNode $node): int
    {
        if (is_null($node)) {
            return -1;
        }
        $leftHeight = $this->checkBalance($node->getLeft());
        $rightHeight = $this->checkBalance($node->getRight());
        if ($leftHeight === -1 || $rightHeight === -1) {
            return -1;
        }
        if (abs($leftHeight - $rightHeight) > 1) {
            return -1;
        }
        return 1 + max($leftHeight, $rightHeight);
    }

    private function generateTraversal(?BinaryTreeNode $node): Generator
    {
        if (is_null($node)) {
            return;
        }

        yield from $this->generateTraversal($node->getLeft());
        yield $node->getValue();
        yield from $this->generateTraversal($node->getRight());
    }

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return $this->generateTraversal($this->root);
    }

    public function count(): int
    {

        return $this->size;
    }
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

    public function toArray(): array
    {
        return $this->levelOrder();
    }



    public function getDiameter(): int
    {
        $this->maxDiameter = 0;
        $this->calculateHeight($this->root);
        return $this->maxDiameter;
    }

    private function calculateHeight(?BinaryTreeNode $node): int
    {
        if (is_null($node)) {
            return -1;
        }

        $leftHeight = $this->calculateHeight($node->getLeft());
        $rightHeight = $this->calculateHeight($node->getRight());

        $currentDiameter = $leftHeight + $rightHeight + 1;
        $this->maxDiameter = max($this->maxDiameter, $currentDiameter);

        return 1 + max($leftHeight, $rightHeight);
    }
}
