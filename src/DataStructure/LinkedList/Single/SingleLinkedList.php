<?php

declare(strict_types=1);

namespace Zack\PhpDsAlgo\DataStructure\LinkedList\Single;

use InvalidArgumentException;
use IteratorAggregate;
use Zack\PhpDsAlgo\Constants\ErrorMessages;
use Zack\PhpDsAlgo\Contracts\ILinkedList;

class SingleLinkedList implements IteratorAggregate, ILinkedList
{
    private ?SingleLinkedListNode $head = null;
    private int $length = 0;
    // Constructor
    private function __construct(?SingleLinkedListNode $head = null, ?int $length = 0)
    {
        $this->head = $head;
        $this->length = $length;
    }

    // Getters and Setters
    public function getLength(): int
    {
        return $this->length;
    }

    public static function ofObjects(array $nodes): self
    {
        return self::fromNodes($nodes);
    }

    public function getHead(): ?SingleLinkedListNode
    {
        return $this->head;
    }
    public function getIterator(): \Traversable
    {
        $current = $this->head;

        while ($current !== null) {
            yield $current; // or yield $current->getValue();
            $current = $current->getNext();
        }
    }
    public static function empty(): self
    {
        return new self(null, 0);
    }

    // methods of creation
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
        for ($i = 0; $i < count($nodes) - 1; $i++) {
            $nodes[$i]->setNext($nodes[$i + 1]);
        }
        return new self($nodes[0], count($nodes));
    }
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



        return new self($head, count($values));
    }
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

        return new self($head, $length);
    }
    // methods of insertions

    private function cloneNodes(): ?SingleLinkedListNode
    {
        if ($this->head === null) {
            return null;
        }

        $old = $this->head;

        $newHead = new SingleLinkedListNode($old->getValue());
        $newCurrent = $newHead;

        $old = $old->getNext();

        while ($old !== null) {
            $node = new SingleLinkedListNode($old->getValue());
            $newCurrent->setNext($node);

            $newCurrent = $node;
            $old = $old->getNext();
        }

        return $newHead;
    }
    public function prepend($value): self
    {
        $newHead = new SingleLinkedListNode($value);
        $newHead->setNext($this->head);
        return new self($newHead, $this->length + 1);
    }
    public function append($value): self
    {
        $newNode = new SingleLinkedListNode($value);

        if ($this->head === null) {
            return new self($newNode, 1);
        }

        $head = $this->cloneNodes();
        $current = $head;

        while ($current->getNext() !== null) {
            $current = $current->getNext();
        }

        $current->setNext($newNode);

        return new self($head, $this->length + 1);
    }
    public function insert($value, int $index): self
    {
        if ($index < 0 || $index > $this->length) {
            throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
        }

        $newNode = new SingleLinkedListNode($value);
        if ($index === 0) {
            return $this->prepend($value);
        }
        $current = $this->head;

        $newHead = $this->cloneNodes();
        // find node BEFORE target position
        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->getNext();
        }

        if ($current === null) {
            throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
        }

        $newNode = new SingleLinkedListNode($value);
        $newNode->setNext($current->getNext());
        $current->setNext($newNode);

        return new self($newHead, $this->length + 1);
    }
    // methods of removal
    public function removeByValue(mixed $value): self
    {
        $head = $this->cloneNodes();

        if ($head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        if ($head->getValue() === $value) {
            return new self($head->getNext(), $this->length - 1);
        }
        $current = $head;

        while ($current->getNext() !== null) {
            if ($current->getNext()->getValue() === $value) {
                $current->setNext($current->getNext()->getNext());
                return new self($head, $this->length - 1);
            }

            $current = $current->getNext();
        }

        throw new InvalidArgumentException(ErrorMessages::NO_NODE_WITH_THIS_VALUE);
    }
    public function clear(): self
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        return new self(null, 0);
    }
    public function clearAndKeepHead(): self
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        $newHead = new SingleLinkedListNode($this->head->getValue());

        return new self($newHead, 1);
    }
    // index start from 0
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
        $nodeToRemove->setNext(null);

        return new self($head, $this->length - 1);
    }
    public function removeHead(): self
    {

        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        return new self(
            $this->head->getNext(),
            $this->length - 1
        );
    }
    public function removeTail(): self
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        if ($this->head->getNext() === null) {
            return new self(null, 0);
        }
        $head = $this->cloneNodes();

        $current = $head;

        while ($current->getNext()->getNext() !== null) {
            $current = $current->getNext();
        }

        $current->setNext(null);

        return new self($head, $this->length - 1);
    }

    // access
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
    public function getTail(): SingleLinkedListNode
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        $current = $this->head;
        while ($current->getNext() !== null) {
            $current = $current->getNext();
        }
        return $current;
    }
    public function contains(mixed $value): SingleLinkedListNode
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        $current = $this->head;
        while ($current !== null) {
            if ($current->getValue() == $value) {
                return $current;
            }
            $current = $current->getNext();
        }
        throw new InvalidArgumentException(ErrorMessages::NO_NODE_WITH_THIS_VALUE);
    }
    public function indexOf(mixed $value): int
    {
        if ($this->head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        $current = $this->head;
        $currentIndex = 0;
        while ($current !== null) {
            if ($current->getValue() == $value) {
                return $currentIndex;
            }
            $current = $current->getNext();
            $currentIndex++;
        }

        throw new InvalidArgumentException(ErrorMessages::NO_NODE_WITH_THIS_VALUE);
    }
    // Transformations
    // two pointer solution
    public function reverse(): self
    {
        $current = $this->head;
        $previous = null;
        if (is_null($current)) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        while ($current !== null) {
            $next = $current->getNext();
            $current->setNext($previous);
            $previous = $current;
            $current = $next;
        }
        return new self($previous, $this->length);
    }
    public function toArray(): array
    {
        if (is_null($this->head)) {
            return [];
        }
        $current = $this->head;

        $nodesArray = [];
        while ($current !== null) {
            $nodesArray[] = $current;
            $current = $current->getNext();
        }
        return $nodesArray;
    }
    public function toArrayValues(): array
    {
        if (is_null($this->head)) {
            return [];
        }
        $current = $this->head;

        $nodesArray = [];
        while ($current !== null) {
            $nodesArray[] = $current->getValue();
            $current = $current->getNext();
        }
        return $nodesArray;
    }
    // Functional methodes
    public function map(callable $fn): self
    {
        if ($this->head === null) {
            return self::empty();
        }

        $values = [];

        $current = $this->head;

        while ($current !== null) {
            $values[] = $fn($current->getValue());
            $current = $current->getNext();
        }

        return self::of($values);
    }
    public function filter(callable $fn): self
    {
        if ($this->head === null) {
            return self::empty();
        }

        $values = [];

        $current = $this->head;

        while ($current !== null) {
            $value = $current->getValue();

            if ($fn($value)) {
                $values[] = $value;
            }

            $current = $current->getNext();
        }

        return self::of($values);
    }
    public function reduce(callable $fn, mixed $initial = null): mixed
    {
        $carry = $initial;

        $current = $this->head;

        while ($current !== null) {
            $carry = $fn($carry, $current->getValue());
            $current = $current->getNext();
        }

        return $carry;
    }
}
