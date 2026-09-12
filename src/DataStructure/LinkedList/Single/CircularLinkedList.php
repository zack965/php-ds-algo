<?php

declare(strict_types=1);

namespace Zack\PhpDsAlgo\DataStructure\LinkedList\Single;

use InvalidArgumentException;
use IteratorAggregate;
use Zack\PhpDsAlgo\Constants\ErrorMessages;
use Zack\PhpDsAlgo\Contracts\ILinkedList;


/**
 * An immutable, circular singly-linked list: like {@see SingleLinkedList},
 * but the tail's `next` points back to the head instead of to `null`, so
 * the chain has no natural end.
 *
 * Follows the same persistent-list pattern as `SingleLinkedList` —
 * `private` constructor reachable only through the static factories below,
 * and every mutating operation returns a **new** instance via
 * {@see cloneNodes()} rather than mutating the receiver. The one added
 * invariant every operation must preserve is circularity: for a non-empty
 * list, `getTail()->getNext()` must always be `getHead()` again. Because
 * there's no `null` terminator to stop at, traversals here are always
 * bounded by the tracked `$length` (see {@see getIterator()}) instead of a
 * `while ($current !== null)` loop.
 */
class CircularLinkedList implements IteratorAggregate, ILinkedList
{
    private ?SingleLinkedListNode $head = null;
    private ?SingleLinkedListNode $tail = null;
    private int $length = 0;
    // Constructor
    /**
     * Only reachable via the static factories ({@see empty()}, {@see of()},
     * {@see fromNodes()}, {@see fromIterable()}) — never `new self()`
     * directly from outside the class.
     */
    private function __construct(
        ?SingleLinkedListNode $head = null,
        ?SingleLinkedListNode $tail = null,
        ?int $length = 0
    ) {
        $this->head = $head;
        $this->tail = $tail;
        $this->length = $length;
    }

    // Getters and Setters
    /**
     * Returns the number of nodes in the list.
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Alias of {@see fromNodes()}.
     */
    public static function ofObjects(array $nodes): self
    {
        return self::fromNodes($nodes);
    }

    /**
     * Returns the head node, or `null` if the list is empty.
     */
    public function getHead(): ?SingleLinkedListNode
    {
        return $this->head;
    }
    /**
     * Iterates over the list's nodes, head to tail, in order.
     *
     * Bounded by `$length` rather than a `null`-next check — a circular
     * list's tail never points to `null`, so a naive `while ($current !==
     * null)` loop here would never terminate.
     *
     * @return \Traversable<int, SingleLinkedListNode>
     */
    public function getIterator(): \Traversable
    {
        $current = $this->head;

        for ($i = 0; $i < $this->length; $i++) {
            yield $current; // or yield $current->getValue();
            $current = $current->getNext();
        }
    }
    /**
     * Creates an empty list.
     */
    public static function empty(): self
    {
        return new self();
    }

    // methods of creation
    /**
     * Builds a new list from an array of {@see SingleLinkedListNode}
     * instances, deep-copying each node's value and linking the copies into
     * a circular chain (the tail's `next` is set back to the new head).
     *
     * @throws InvalidArgumentException if any element is not a
     *                                   {@see SingleLinkedListNode}
     */
    public static function fromNodes(array $nodes): self
    {
        if ($nodes === []) {
            return self::empty();
        }
        foreach ($nodes as $node) {
            if (!$node instanceof SingleLinkedListNode) {
                throw new InvalidArgumentException(
                    'All elements must be instances of SingleLinkedListNode'
                );
            }
        }
        $clonedNodes = [];
        foreach ($nodes as $node) {
            $clonedNodes[] = new SingleLinkedListNode(
                $node->getValue()
            );
        }
        for ($i = 0; $i < count($clonedNodes) - 1; $i++) {
            $clonedNodes[$i]->setNext($clonedNodes[$i + 1]);
        }
        $head = $clonedNodes[0];
        $tail = $clonedNodes[count($clonedNodes) - 1];
        $tail->setNext($head);
        return new self($head, $tail, count($clonedNodes));
    }
    /**
     * Builds a new list from a plain array of values, linking them into a
     * circular chain (the tail's `next` is set back to the new head).
     */
    public static function of(array $values): self
    {
        if (empty($values)) {
            return self::empty();
        }
        $head = new SingleLinkedListNode($values[0]);

        $current = $head;
        for ($i = 1; $i < count($values); $i++) {
            $next = new SingleLinkedListNode($values[$i]);

            $current->setNext($next);
            $current = $next;
        }
        $tail = $current;
        $tail->setNext($head);
        return new self($head, $tail, count($values));
    }
    /**
     * Builds a new list from any iterable of values (consumed fully,
     * including generators), linking them into a circular chain.
     */
    public static function fromIterable(iterable $values): self
    {
        $head = null;
        $current = null;
        $length = 0;

        foreach ($values as $value) {
            $node = new SingleLinkedListNode($value);

            if ($head === null) {
                $head = $node;
                $current = $node;
            } else {
                $current->setNext($node);
                $current = $node;
            }

            $length++;
        }
        if ($head !== null && $current !== null) {
            $current->setNext($head);
        }
        return new self($head, $current, $length);
    }
    // methods of insertions    
    /**
     * Insert $value immediately before the first node holding $target.
     *
     * @throws InvalidArgumentException if $target is not found
     */
    public function insertBeforeNode(mixed $target, mixed $value): self
    {
        $index = $this->indexOf($target);
        return $this->insert($value, $index);
    }
    /**
     * Insert $value immediately after the first node holding $target.
     *
     * @throws InvalidArgumentException if $target is not found
     */
    public function insertAfterNode(mixed $target, mixed $value): self
    {
        $index = $this->indexOf($target);
        return $this->insert($value, $index + 1);
    }
    /**
     * Deep-copies the existing chain (all `$length` nodes) into a new,
     * still-circular chain and returns its head, so a mutating method can
     * splice its change into the copy and leave the receiver untouched.
     *
     * @return SingleLinkedListNode|null The new head, or `null` if the list is empty.
     */
    private function cloneNodes(): ?SingleLinkedListNode
    {
        if ($this->head === null) {
            return null;
        }

        $old = $this->head;

        $newHead = new SingleLinkedListNode($old->getValue());
        $newCurrent = $newHead;

        for ($i = 1; $i < $this->length; $i++) {
            $old = $old->getNext();

            $node = new SingleLinkedListNode($old->getValue());

            $newCurrent->setNext($node);
            $newCurrent = $node;
        }

        $newCurrent->setNext($newHead);

        return $newHead;
    }
    /**
     * Returns a new list with $value inserted before the current head.
     */
    public function prepend(mixed $value): self
    {
        $newHead = new SingleLinkedListNode($value);
        if ($this->head === null) {
            $newHead->setNext($newHead);

            return new self(
                $newHead,
                $newHead,
                1
            );
        }
        $oldHead = $this->cloneNodes();
        $newHead->setNext($oldHead);
        $newTail = $oldHead;

        for ($i = 1; $i < $this->length; $i++) {
            $newTail = $newTail->getNext();
        }

        $newTail->setNext($newHead);
        return new self(
            $newHead,
            $newTail,
            $this->length + 1
        );
    }
    /**
     * Returns a new list with $value inserted after the current tail.
     */
    public function append(mixed $value): self
    {
        $newNode = new SingleLinkedListNode($value);

        if ($this->head === null) {
            $newNode->setNext($newNode);
            return new self(
                $newNode,
                $newNode,
                1
            );
        }
        $newHead = $this->cloneNodes();
        $newTail = $newHead;

        for ($i = 1; $i < $this->length; $i++) {
            $newTail = $newTail->getNext();
        }

        $newTail->setNext($newNode);
        $newNode->setNext($newHead);
        return new self(
            $newHead,
            $newNode,
            $this->length + 1
        );
    }
    /**
     * Returns a new list with $value inserted at $index (0-based; $index ===
     * getLength() appends).
     *
     * @throws InvalidArgumentException if $index is out of bounds
     */
    public function insert(mixed $value, int $index): self
    {
        if ($index < 0 || $index > $this->length) {
            throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
        }
        if ($index === 0) {
            return $this->prepend($value);
        }


        $newHead = $this->cloneNodes();
        $previousNode = $newHead;
        // find node BEFORE target position
        for ($i = 0; $i < $index - 1; $i++) {
            $previousNode = $previousNode->getNext();
        }


        $newNode = new SingleLinkedListNode($value);

        $newNode->setNext($previousNode->getNext());
        $previousNode->setNext($newNode);
        $newTail = $newHead;
        for ($i = 1; $i < $this->length + 1; $i++) {
            $newTail = $newTail->getNext();
        }

        if ($index === $this->length) {
            $newTail = $newNode;
        }
        return new self($newHead, $newTail, $this->length + 1);
    }
    // methods of removal
    /**
     * Returns a new list with the first node holding $value removed.
     *
     * @throws InvalidArgumentException if the list is empty, or if no node
     *                                   holds $value
     */
    public function removeByValue(mixed $value): self
    {

        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        $head = $this->cloneNodes();
        if ($this->length === 1) {
            if ($head->getValue() === $value) {
                return self::empty();
            }

            throw new InvalidArgumentException(
                ErrorMessages::NO_NODE_WITH_THIS_VALUE
            );
        }
        if ($head->getValue() === $value) {
            $newHead = $head->getNext();
            $tail = $newHead;
            for ($i = 1; $i < $this->length - 1; $i++) {
                $tail = $tail->getNext();
            }
            $tail->setNext($newHead);
            return new self(
                $newHead,
                $tail,
                $this->length - 1
            );
        }
        $current = $head;

        for ($i = 0; $i < $this->length - 1; $i++) {
            $next = $current->getNext();
            if ($next->getValue() === $value) {
                $current->setNext($next->getNext());
                $newTail = $current;
                if ($i !== $this->length - 2) {
                    $newTail = $this->findTail($head, $this->length - 1);
                }

                return new self(
                    $head,
                    $newTail,
                    $this->length - 1
                );
            }

            $current = $next;
        }

        throw new InvalidArgumentException(ErrorMessages::NO_NODE_WITH_THIS_VALUE);
    }

    /**
     * Walks `$length - 1` steps forward from $head and returns the node
     * reached — i.e. the last node of a (still-circular) chain of exactly
     * $length nodes starting at $head.
     */
    private function findTail(SingleLinkedListNode $head,  int $length): SingleLinkedListNode
    {
        $current = $head;

        for ($i = 1; $i < $length; $i++) {
            $current = $current->getNext();
        }


        return $current;
    }
    /**
     * Returns a new, empty list.
     *
     * @throws InvalidArgumentException if the list is already empty
     */
    public function clear(): self
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        return new self();
    }
    /**
     * Returns a new single-node list holding just the current head's value
     * (self-linked, so it stays circular).
     *
     * @throws InvalidArgumentException if the list is empty
     */
    public function clearAndKeepHead(): self
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        $newHead = new SingleLinkedListNode($this->head->getValue());
        $newHead->setNext($newHead);
        return new self(
            $newHead,
            $newHead,
            1
        );
    }
    // index start from 0
    /**
     * Returns a new list with the node at $index (0-based) removed.
     *
     * @throws InvalidArgumentException if $index is out of bounds, or if
     *                                   the list is empty
     */
    public function removeAt(int $index): self
    {
        if ($index < 0 || $index >= $this->length) {
            throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
        }

        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        if ($index === 0) {
            return $this->removeHead();
        }

        $head = $this->cloneNodes();

        $current = $head;

        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->getNext();
        }

        $nodeToRemove = $current->getNext();
        $current->setNext($nodeToRemove->getNext());
        // Removing the tail

        $tail = $this->findTail(
            $head,
            $this->length - 1
        );
        return new self(
            $head,
            $tail,
            $this->length - 1
        );
    }
    /**
     * Returns a new list with the head node removed.
     *
     * @throws InvalidArgumentException if the list is empty
     */
    public function removeHead(): self
    {

        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        if ($this->length === 1) {
            return self::empty();
        }

        $newHead = $this->cloneNodes();

        $newHead = $newHead->getNext();

        $newTail = $newHead;

        for ($i = 1; $i < $this->length - 1; $i++) {
            $newTail = $newTail->getNext();
        }
        $newTail->setNext($newHead);
        return new self(
            $newHead,
            $newTail,
            $this->length - 1
        );
    }
    /**
     * Returns a new list with the tail node removed.
     *
     * @throws InvalidArgumentException if the list is empty
     */
    public function removeTail(): self
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        if ($this->length === 1) {
            return self::empty();
        }


        $head = $this->cloneNodes();

        $newTail = $head;
        for ($i = 1; $i < $this->length - 1; $i++) {
            $newTail = $newTail->getNext();
        }

        $newTail->setNext($head);

        return new self(
            $head,
            $newTail,
            $this->length - 1
        );
    }

    // access


    /**
     * get Node object based on index
     *
     * @param  mixed $index
     * @return SingleLinkedListNode
     */
    public function get(int $index): SingleLinkedListNode
    {
        if ($index < 0 || $index >= $this->length) {
            throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
        }
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }


        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            $current = $current->getNext();
        }

        return $current;
    }
    /**
     * Returns the tail node (whose `next` points back to the head).
     *
     * @throws InvalidArgumentException if the list is empty
     */
    public function getTail(): SingleLinkedListNode
    {
        if ($this->tail === null) {
            throw new InvalidArgumentException(
                ErrorMessages::LINKEDLIST_IS_EMPTY
            );
        }
        return $this->tail;
    }
    /**
     * Returns the first node holding $value.
     *
     * @throws InvalidArgumentException if the list is empty, or if no node
     *                                   holds $value
     */
    public function contains(mixed $value): SingleLinkedListNode
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        $current = $this->head;
        for ($i = 0; $i < $this->length; $i++) {
            if ($current->getValue() === $value) {
                return $current;
            }

            $current = $current->getNext();
        }
        throw new InvalidArgumentException(ErrorMessages::NO_NODE_WITH_THIS_VALUE);
    }
    /**
     * Returns the index (0-based) of the first node holding $value.
     *
     * @throws InvalidArgumentException if the list is empty, or if no node
     *                                   holds $value
     */
    public function indexOf(mixed $value): int
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        $current = $this->head;
        for ($currentIndex = 0; $currentIndex < $this->length; $currentIndex++) {
            if ($current->getValue() === $value) {
                return $currentIndex;
            }
            $current = $current->getNext();
        }

        throw new InvalidArgumentException(ErrorMessages::NO_NODE_WITH_THIS_VALUE);
    }
    // Transformations
    // two pointer solution
    /**
     * Returns a new list with the node order reversed (the old head becomes
     * the new tail and vice versa; the list stays circular).
     *
     * @throws InvalidArgumentException if the list is empty
     */
    public function reverse(): self
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(
                ErrorMessages::LINKEDLIST_IS_EMPTY
            );
        }

        if ($this->length === 1) {
            return new self(
                $this->head,
                $this->tail,
                1
            );
        }
        $head = $this->cloneNodes();
        $current = $head;
        $previous = null;
        for ($i = 0; $i < $this->length; $i++) {
            $next = $current->getNext();
            $current->setNext($previous);
            $previous = $current;
            $current = $next;
        }
        $newHead = $previous;
        $newTail = $head;
        $newTail->setNext($newHead);
        return new self(
            $newHead,
            $newTail,
            $this->length
        );
    }
    /**
     * Returns the list's nodes, head to tail, as a plain array.
     *
     * @return list<SingleLinkedListNode>
     */
    public function toArray(): array
    {
        if (is_null($this->head)) {
            return [];
        }
        $current = $this->head;

        $nodesArray = [];
        for ($i = 0; $i < $this->length; $i++) {
            $nodesArray[] = $current;
            $current = $current->getNext();
        }
        return $nodesArray;
    }
    /**
     * Returns the list's values, head to tail, as a plain array.
     *
     * @return list<mixed>
     */
    public function toArrayValues(): array
    {
        if (is_null($this->head)) {
            return [];
        }
        $current = $this->head;

        $nodesArray = [];
        for ($i = 0; $i < $this->length; $i++) {
            $nodesArray[] = $current->getValue();
            $current = $current->getNext();
        }
        return $nodesArray;
    }
    // Functional methodes
    /**
     * Returns a new list with $fn applied to every value, head to tail.
     */
    public function map(callable $fn): self
    {
        if ($this->head === null) {
            return self::empty();
        }

        $values = [];

        $current = $this->head;

        for ($i = 0; $i < $this->length; $i++) {
            $values[] = $fn($current->getValue());
            $current = $current->getNext();
        }

        return self::of($values);
    }
    /**
     * Returns a new list containing only the values for which $fn returns
     * a truthy result.
     */
    public function filter(callable $fn): self
    {
        if ($this->head === null) {
            return self::empty();
        }

        $values = [];

        $current = $this->head;

        for ($i = 0; $i < $this->length; $i++) {
            $value = $current->getValue();

            if ($fn($value)) {
                $values[] = $value;
            }

            $current = $current->getNext();
        }

        return self::of($values);
    }
    /**
     * Reduces the list's values, head to tail, to a single value via $fn,
     * starting from $initial.
     */
    public function reduce(callable $fn, mixed $initial = null): mixed
    {
        $carry = $initial;

        $current = $this->head;

        for ($i = 0; $i < $this->length; $i++) {
            $carry = $fn($carry, $current->getValue());
            $current = $current->getNext();
        }

        return $carry;
    }
}
