<?php


namespace Zack\PhpDsAlgo\DataStructure\Tree;

use Generator;
use Traversable;
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;
use Zack\PhpDsAlgo\Contracts\Tree\ITree;
use Zack\PhpDsAlgo\DataStructure\Queue\Queue;
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

/**
 * Generic tree.
 *
 * @template T
 *
 * @implements ITree<T>
 */
abstract class AbstractTree implements ITree
{
    /**
     * @var BinaryTreeNode<T>|null
     */
    protected ?BinaryTreeNode $root = null;
    protected int $size = 0;

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


    protected function getLeftChildIndex(int $index): int
    {
        return 2 * $index + 1;
    }

    protected function getRightChildIndex(int $index): int
    {
        return 2 * $index + 2;
    }

    protected function getParentIndex(int $index): int
    {
        return (int) floor(($index - 1) / 2);
    }

    /**
     * Returns the root node.
     */
    public function getRoot(): ?BinaryTreeNode
    {
        return $this->root;
    }

    /**
     * Determines whether the tree is empty.
     */


    public function isEmpty(): bool
    {
        return $this->size === 0;
    }
    /**
     * Removes all nodes from the tree.
     */
    public function clear(): void
    {
        $this->root = null;
        $this->size = 0;
    }
    /**
     * Returns the height of the tree.
     */
    public function getHeight(): int
    {
        return $this->getNodeHeight($this->root);
    }
    protected function getNodeHeight(?BinaryTreeNode $node)
    {
        if (is_null($node)) {
            return -1;
        }
        $leftNodeHeight = $this->getNodeHeight($node->getLeft());
        $rightNodeHeight = $this->getNodeHeight($node->getRight());
        return 1 + max($leftNodeHeight, $rightNodeHeight);
    }

    /**
     * Determines whether the tree contains the given value.
     *
     * @param T $value
     */
    public function contains(mixed $value): bool
    {

        if (is_null($this->root)) {
            return false;
        }
        $queue = new Queue();
        $queue->enqueue($this->root);
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $node */
            $node = $queue->dequeue();
            if ($node->getValue() === $value) {
                return true;
            }
            if ($node->getLeft()) {
                $queue->enqueue($node->getLeft());
            }
            if ($node->getRight()) {
                $queue->enqueue($node->getRight());
            }
        }
        return false;
    }
    /**
     * Returns the values using level-order traversal.
     *
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
    /**
     * Converts the tree to an array.
     *
     * @return list<T>
     */
    public function toArray(): array
    {
        return $this->levelOrder();
    }

    /**
     * Returns an iterator over the tree values.
     *
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return $this->generateTraversal($this->root);
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
     * Returns the number of nodes in the tree.
     */
    public function count(): int
    {
        return $this->size;
    }
}
