<?php


namespace Zack\PhpDsAlgo\DataStructure\Tree;

use InvalidArgumentException;
use Zack\PhpDsAlgo\Contracts\Tree\IBinarySearchTree;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList;
use Zack\PhpDsAlgo\DataStructure\Queue\Queue;

/**
 * Binary Search Tree implementation.
 * @template T of int|float|string|\Stringable
 * @implements IBinarySearchTree<T>
 */
class BinarySearchTree extends AbstractTree implements IBinarySearchTree
{
    /**
     * Constructor.
     *
     * Creates a new BST, optionally from an array of values.
     *
     * @param list<T>|null $values Array of values to initialize the tree
     * @throws InvalidArgumentException If values contain unsupported types
     */
    public function __construct(?array $values = null)
    {
        if ($values !== null) {
            foreach ($values as $value) {
                $this->insert($value);
            }
        }
    }

    /**
     * Returns the node containing the minimum value.
     * O(log n) average, O(n) worst case.
     *
     * @return BinaryTreeNode<T>|null
     */
    public function min(): ?BinaryTreeNode
    {
        if (is_null($this->root)) {
            return null;
        }

        $queue = new Queue();
        $queue->enqueue($this->root);
        $smallest_node = $this->root;
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $node */
            $node = $queue->dequeue();
            $leftChild = $node->getLeft();
            $rightChild = $node->getRight();

            if ($node->getValue() < $smallest_node->getValue()) {
                $smallest_node = $node;
            }
            if ($leftChild) {
                $queue->enqueue($leftChild);
            }
            if ($rightChild) {
                $queue->enqueue($rightChild);
            }
        }

        return $smallest_node;
    }

    /**
     * Returns the node containing the maximum value.
     * O(log n) average, O(n) worst case.
     *
     * @return BinaryTreeNode<T>|null
     */
    public function max(): ?BinaryTreeNode
    {
        if (is_null($this->root)) {
            return null;
        }

        $queue = new Queue();
        $queue->enqueue($this->root);
        $biggest_node = $this->root;
        while (!$queue->isEmpty()) {
            /** @var BinaryTreeNode<T> $node */
            $node = $queue->dequeue();
            $leftChild = $node->getLeft();
            $rightChild = $node->getRight();

            if ($node->getValue() > $biggest_node->getValue()) {
                $biggest_node = $node;
            }
            if ($leftChild) {
                $queue->enqueue($leftChild);
            }
            if ($rightChild) {
                $queue->enqueue($rightChild);
            }
        }

        return $biggest_node;
    }

    /**
     * Inserts a value into the BST maintaining BST order.
     * Overrides IBinaryTree::insert to ensure BST property.
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return static
     */
    public function insert(mixed $value): static
    {
        $newNode = new BinaryTreeNode($value);
        if (is_null($this->root)) {
            $this->root = $newNode;
            $this->size++;
            return $this;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        while (true) {

            if ($current->getValue() > $value) {

                if ($current->getLeft() === null) {
                    $current->setLeft($newNode);
                    $this->size++;
                    return $this;
                } else {
                    $current = $current->getLeft();
                }
            } elseif ($current->getValue() < $value) {

                if ($current->getRight() === null) {
                    $current->setRight($newNode);
                    $this->size++;
                    return $this;
                } else {
                    $current = $current->getRight();
                }
            } else {
                // duplicates are ignored
                return $this;
            }
        }
    }
    /**
     * Returns the predecessor of a value (largest value < given value).
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return BinaryTreeNode<T>|null
     */
    public function predecessor(mixed $value): ?BinaryTreeNode
    {
        if (is_null($this->root)) {
            return null;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        $predecessor = null;
        while (!is_null($current)) {

            if ($current->getValue() <  $value) {
                $predecessor = $current;
                $current = $current->getRight();
            } elseif ($current->getValue() >  $value) {
                $current = $current->getLeft();
            } else {
                // value founded

                if (!is_null($current->getLeft())) {
                    $current = $current->getLeft();

                    while (!is_null($current->getRight())) {
                        $current = $current->getRight();
                    }
                    return $current;
                } else {
                    return $predecessor;
                }
            }
        }
        return $predecessor;
    }
    /**
     * Returns the successor of a value (smallest value > given value).
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return BinaryTreeNode<T>|null
     */
    public function successor(mixed $value): ?BinaryTreeNode
    {
        if (is_null($this->root)) {
            return null;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        $successor = null;
        while (!is_null($current)) {

            if ($current->getValue() <  $value) {
                $current = $current->getRight();
            } elseif ($current->getValue() >  $value) {
                $successor = $current;
                $current = $current->getLeft();
            } else {
                // value founded

                if (!is_null($current->getRight())) {
                    $current = $current->getRight();

                    while (!is_null($current->getLeft())) {
                        $current = $current->getLeft();
                    }
                    return $current;
                } else {
                    return $successor;
                }
            }
        }
        return $successor;
    }
    /**
     * Returns the floor (largest value ≤ target).
     * O(log n) average, O(n) worst case.
     *
     * @param T $target
     * @return T|null
     */
    public function floor(mixed $target): mixed
    {
        if (is_null($this->root)) {
            return null;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        $closest = null;
        while (!is_null($current)) {
            if ($target >= $current->getValue()) {
                $closest = $current->getValue();
                $current = $current->getRight();
            } elseif ($target < $current->getValue()) {
                $current = $current->getLeft();
            }
        }
        if (is_null($closest)) {
            return null;
        }
        return $closest;
    }
    /**
     * Finds the closest value to the target.
     * O(log n) average, O(n) worst case.
     *
     * @param T $target
     * @return T|null Returns null if tree is empty
     */
    public function findClosest(mixed $target): mixed
    {
        if (is_null($this->root)) {
            return null;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        $minDistance = null;
        $minNode = null;
        while (!is_null($current)) {
            if ($target > $current->getValue()) {
                $distance = abs($target - $current->getValue());

                if (is_null($minDistance) || $distance < $minDistance) {
                    $minNode = $current;
                    $minDistance = $distance;
                }
                $current = $current->getRight();
            } elseif ($target < $current->getValue()) {
                $distance = abs($target - $current->getValue());

                if (is_null($minDistance) || $distance < $minDistance) {
                    $minNode = $current;
                    $minDistance = $distance;
                }
                $current = $current->getLeft();
            } else {
                return $current->getValue();
            }
        }

        return $minNode->getValue();
    }
    /**
     * Creates a BST from an array of values.
     * Duplicate values are ignored.
     *
     * @param list<T> $values
     * @return static
     */
    public static function fromArray(array $values): static
    {
        return new self($values);
    }
    /**
     * Searches for a node by value using BST properties.
     * Overrides IBinaryTree::search for optimized O(log n) search.
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return BinaryTreeNode<T>|null
     */
    public function search(mixed $value): ?BinaryTreeNode
    {
        if (is_null($this->root)) {
            return null;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        while (!is_null($current)) {
            if ($value > $current->getValue()) {
                $current = $current->getRight();
            } elseif ($value < $current->getValue()) {
                $current = $current->getLeft();
            } else {
                return $current;
            }
        }

        return null;
    }
    /**
     * Removes a value from the BST maintaining BST order.
     * Overrides IBinaryTree::remove to ensure BST property.
     * O(log n) average, O(n) worst case.
     *
     * @param T $value
     * @return static
     */
    public function remove(mixed $value): static
    {
        if (is_null($this->root)) {
            return $this;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        /** @var BinaryTreeNode<T>|null $parent */
        $parent = null;
        while (!is_null($current)) {

            if ($value > $current->getValue()) {
                $parent = $current;
                $current = $current->getRight();
            } elseif ($value < $current->getValue()) {
                $parent = $current;
                $current = $current->getLeft();
            } else {
                break;
            }
        }
        if (is_null($current)) {
            return $this;
        }
        // start deleting

        if ($this->isLeaf($current)) {
            // CASE 1: Node has NO children (leaf node)
            // Only has right child
            if (is_null($parent)) {
                $this->root = null;
            } elseif ($parent->getLeft() == $current) {
                $parent->setLeft(null);
            } else {
                $parent->setRight(null);
            }
        } elseif ($this->hasRightChild($current) && !$this->hasLeftChild($current)) {
            // CASE 2: Node has ONLY ONE child
            if (is_null($parent)) {
                $this->root = $current->getRight();
            } elseif ($parent->getLeft() == $current) {
                $parent->setLeft($current->getRight());
            } else {
                $parent->setRight($current->getRight());
            }
        } elseif ($this->hasLeftChild($current) && !$this->hasRightChild($current)) {
            // Only has left child
            if (is_null($parent)) {
                $this->root = $current->getLeft();
            } elseif ($parent->getLeft() == $current) {
                $parent->setLeft($current->getLeft());
            } else {
                $parent->setRight($current->getLeft());
            }
        } else {
            // CASE 3: Node has TWO children
            // Find successor (minimum in right subtree)
            $successor = $current->getRight();
            $successorParent = $current;
            while (!is_null($successor->getLeft())) {
                $successorParent = $successor;
                $successor = $successor->getLeft();
            }
            // Copy successor's value to current node
            $current->setValue($successor->getValue());
            if ($successorParent->getLeft() == $successor) {
                $successorParent->setLeft($successor->getRight());
            } else {
                $successorParent->setRight($successor->getRight());
            }
        }
        $this->size--;

        return $this;
    }
    /**
     * Validates that the tree satisfies BST invariants.
     * For every node: left subtree values < node value < right subtree values.
     * O(n) time.
     */
    public function isValid(): bool
    {
        if ($this->root === null) {
            return true;
        }
        $previous = null;
        return $this->inOrderCheck($this->root, $previous);
    }
    /**
     * Recursively checks if the subtree rooted at the given node satisfies BST order.
     * Uses in-order traversal: left → root → right.
     * Values must be strictly increasing: previous < current < next.
     * @param BinaryTreeNode<T> $node    The current node being checked
     * @param T|null            &$previous The previous value visited (passed by reference)
     * @return bool True if the subtree is a valid BST, false otherwise
     */
    private function inOrderCheck(BinaryTreeNode $node, mixed &$previous)
    {
        if ($node === null) {
            return true;
        }
        if (!$this->inOrderCheck($node->getLeft(), $previous)) {
            return false;
        }
        if (!is_null($previous) && $previous >= $node->getValue()) {
            return false;
        }
        $previous = $node->getValue();
        return $this->inOrderCheck($node->getRight(), $previous);
    }
    /**
     * Returns the ceiling (smallest value ≥ target).
     * O(log n) average, O(n) worst case.
     *
     * @param T $target
     * @return T|null
     */
    public function ceiling(mixed $target): mixed
    {
        if (is_null($this->root)) {
            return null;
        }
        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        $closest = null;
        while (!is_null($current)) {
            if ($target > $current->getValue()) {
                $current = $current->getRight();
            } elseif ($target <= $current->getValue()) {
                $closest = $current->getValue();

                $current = $current->getLeft();
            }
        }
        if (is_null($closest)) {
            return null;
        }
        return $closest;
    }

    /**
     * Returns all values in the inclusive range [low, high] in sorted order.
     * O(n) time, O(m) space where m is the number of values in range.
     *
     * @param T $low
     * @param T $high
     * @return list<T>
     */
    public function rangeSearch(mixed $low, mixed $high): array
    {
        $results = [];
        $this->rangeSearchTraversal($this->root, $results, $low, $high);
        return $results;
    }
    private function rangeSearchTraversal(?BinaryTreeNode $node, array &$results, mixed $low, mixed $heigh)
    {
        if ($node === null) {
            return;
        }
        if ($node->getValue() < $low) {
            $this->rangeSearchTraversal($node->getRight(), $results, $low, $heigh);
        } elseif ($node->getValue() > $heigh) {
            $this->rangeSearchTraversal($node->getLeft(), $results, $low, $heigh);
        } else {
            $this->rangeSearchTraversal($node->getLeft(), $results, $low, $heigh);
            $results[] = $node->getValue();
            $this->rangeSearchTraversal($node->getRight(), $results, $low, $heigh);
        }
    }
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
     * Counts nodes with values in the inclusive range [low, high].
     * O(n) time.
     *
     * @param T $low
     * @param T $high
     * @return int
     */
    public function countInRange(mixed $low, mixed $high): int
    {
        $count = 0;
        $this->rangeSearchTraversalCount($this->root, $count, $low, $high);
        return $count;
    }
    private function rangeSearchTraversalCount(?BinaryTreeNode $node, int &$count, mixed $low, mixed $heigh)
    {
        if ($node === null) {
            return;
        }
        if ($node->getValue() < $low) {
            $this->rangeSearchTraversalCount($node->getRight(), $count, $low, $heigh);
        } elseif ($node->getValue() > $heigh) {
            $this->rangeSearchTraversalCount($node->getLeft(), $count, $low, $heigh);
        } else {
            $this->rangeSearchTraversalCount($node->getLeft(), $count, $low, $heigh);
            $count++;
            $this->rangeSearchTraversalCount($node->getRight(), $count, $low, $heigh);
        }
    }

    /**
     * Returns the kth smallest element (1-indexed).
     * O(n) time for unsorted tree, can be O(log n) if augmented with subtree sizes.
     *
     * @param int $k
     * @return T|null Returns null if k is out of bounds
     */
    public function kthSmallest(int $k): mixed
    {
        if (is_null($this->root)) {
            return null;
        }
        $counter = 0;
        return $this->kthSmallestTraversal($this->root, $k, $counter);
    }
    private function kthSmallestTraversal(?BinaryTreeNode $node, int $k, int &$counter): mixed
    {
        if (is_null($node)) {
            return null;
        }
        $result =  $this->kthSmallestTraversal($node->getLeft(), $k, $counter);
        if (!is_null($result)) {
            return $result;
        }
        $counter++;
        if ($counter == $k) {
            return $node->getValue();
        }
        return $this->kthSmallestTraversal($node->getRight(), $k, $counter);
    }

    /**
     * Returns the kth largest element (1-indexed).
     * O(n) time for unsorted tree, can be O(log n) if augmented with subtree sizes.
     *
     * @param int $k
     * @return T|null Returns null if k is out of bounds
     */
    public function kthLargest(int $k): mixed
    {
        if (is_null($this->root)) {
            return null;
        }
        $counter = 0;
        return $this->kthLargestTraversal($this->root, $k, $counter);
    }

    private function kthLargestTraversal(?BinaryTreeNode $node, int $k, int &$counter): mixed
    {
        if (is_null($node)) {
            return null;
        }
        $result =  $this->kthLargestTraversal($node->getRight(), $k, $counter);
        if (!is_null($result)) {
            return $result;
        }
        $counter++;
        if ($counter == $k) {
            return $node->getValue();
        }
        return $this->kthLargestTraversal($node->getLeft(), $k, $counter);
    }

    /**
     * Returns the lowest common ancestor of two values.
     * O(log n) average, O(n) worst case.
     *
     * @param T $a
     * @param T $b
     * @return BinaryTreeNode<T>|null Returns null if either value not found
     */
    public function lowestCommonAncestor(mixed $a, mixed $b): ?BinaryTreeNode
    {
        if (is_null($this->root)) {
            return null;
        }
        if (is_null($this->search($a)) || is_null($this->search($b))) {
            return null;
        }

        /** @var BinaryTreeNode<T> $current */
        $current = $this->root;
        while (!is_null($current)) {
            if ($a < $current->getValue() && $b < $current->getValue()) {
                $current = $current->getLeft();
            } elseif ($a > $current->getValue() && $b > $current->getValue()) {
                $current = $current->getRight();
            } else {
                return $current;
            }
        }
        return null;
    }

    /**
     * Balances the tree to maintain optimal O(log n) operations.
     * Implements Day-Stout-Warren (DSW) algorithm or AVL rotation.
     * O(n) time.
     *
     * @return static
     */
    public function balance(): static
    {
        if (is_null($this->root) || $this->size <= 1) {
            return $this;
        }

        $this->createVine();
        $this->compressVine();

        return $this;
    }

    private function compressVine(): void
    {
        $n = $this->size;
        $m = pow(2, floor(log($n + 1, 2))) - 1;

        $dummy = new BinaryTreeNode(null);
        $dummy->setRight($this->root);
        // first compress
        $this->compress($dummy, $n - $m);
        while ($m > 1) {
            $m = floor($m / 2);
            $this->compress($dummy, $m);
        }
        $this->root = $dummy->getRight();
    }

    private function compress(BinaryTreeNode $dummy, int $count): void
    {
        $current = $dummy;
        for ($i = 0; $i < $count; $i++) {
            if (is_null($current) || is_null($current->getRight()) || is_null($current->getRight()->getRight())) {
                break;
            }
            // Perform LEFT rotation
            $rightChild = $current->getRight();
            $rightGrandchild = $rightChild->getRight();
            // The rotation
            $current->setRight($rightGrandchild);
            $rightChild->setRight($rightGrandchild->getLeft());
            $rightGrandchild->setLeft($rightChild);
            // Move current to next position
            $current = $rightGrandchild;
        }
    }

    private function createVine(): void
    {
        if (is_null($this->root)) {
            return;
        }
        $dummy = new BinaryTreeNode(null);
        $dummy->setRight($this->root);
        /** @var BinaryTreeNode<T> $dummy */
        $current = $dummy;
        while (!is_null($current)) {
            if (!is_null($current->getRight()) && !is_null($current->getRight()->getLeft())) {
                // Get the left child of current's right child
                $child = $current->getRight()->getLeft();

                // Rotate right
                $current->getRight()->setLeft($child->getRight());
                $child->setRight($current->getRight());
                $current->setRight($child);
            } else {
                $current = $current->getRight();
            }
        }
        $this->root = $dummy->getRight();
    }
}
