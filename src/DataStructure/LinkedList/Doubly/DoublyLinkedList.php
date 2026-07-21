<?php


namespace Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly;

use InvalidArgumentException;
use IteratorAggregate;
use Zack\PhpDsAlgo\Constants\ErrorMessages;
use Zack\PhpDsAlgo\Contracts\IDoublyLinkedList;

class DoublyLinkedList implements IteratorAggregate, IDoublyLinkedList
{
    private ?DoublyLinkedListNode $head = null;
    private int $length = 0;
    private function __construct(?DoublyLinkedListNode $head = null, ?int $length = 0)
    {
        $this->head = $head;
        $this->length = $length;
    }
    public function getLength(): int
    {
        return $this->length;
    }
    public function getHead(): ?DoublyLinkedListNode
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
    /**
     * ofObjects
     *
     * @param  DoublyLinkedListNode[] $nodes
     * @return self
     */
    public static function ofObjects(array $nodes): self
    {
        return self::fromNodes($nodes);
    }

    /**
     * fromNodes
     *
     * @param  DoublyLinkedListNode[] $nodes
     * @return self
     */
    public static function fromNodes(array $nodes): self
    {
        if ($nodes === []) {
            return self::empty();
        }
        foreach ($nodes as $node) {
            if (!$node instanceof DoublyLinkedListNode) {
                throw new InvalidArgumentException(
                    'All elements must be instances of SingleLinkedListNode'
                );
            }
        }
        for ($i = 0; $i < count($nodes) - 1; $i++) {
            $nodes[$i]->setNext($nodes[$i + 1]);
            $nodes[$i + 1]->setPrevious($nodes[$i]);
        }
        return new self($nodes[0], count($nodes));
    }

    /**
     * of
     *
     * @param  mixed $values
     * @return self
     */
    public static function of(array $values): self
    {
        if (empty($values)) {
            return self::empty();
        }
        $head = new DoublyLinkedListNode($values[0]);

        $current = $head;
        for ($i = 1; $i < count($values); $i++) {
            $next = new DoublyLinkedListNode($values[$i]);

            $current->setNext($next);
            $next->setPrevious($current);

            $current = $next;
        }



        return new self($head, count($values));
    }
    /**
     * fromIterable
     *
     * @param  mixed $values
     * @return self
     */
    public static function fromIterable(iterable $values): self
    {
        $head = null;
        $current = null;
        $length = 0;

        for ($i = 0, $count = count($values); $i < $count; $i++) {
            $value = $values[$i];
            $node = new DoublyLinkedListNode($value);

            if ($head === null) {
                $head = $node;
                $current = $node;
            } else {
                $current->setNext($node);
                $node->setPrevious($current);

                $current = $node;
            }

            $length++;
        }

        return new self($head, $length);
    }

    private function cloneNodes(): ?DoublyLinkedListNode
    {
        if ($this->head === null) {
            return null;
        }

        $old = $this->head;

        $newHead = new DoublyLinkedListNode($old->getValue());
        $newCurrent = $newHead;

        $old = $old->getNext();

        while ($old !== null) {
            $node = new DoublyLinkedListNode($old->getValue());
            $newCurrent->setNext($node);
            $node->setPrevious($newCurrent);

            $newCurrent = $node;
            $old = $old->getNext();
        }

        return $newHead;
    }
    public function prepend(mixed $value): self
    {
        $head = $this->cloneNodes();
        $newHead = new DoublyLinkedListNode($value);
        $newHead->setNext($head);
        if ($head !== null) {
            $head->setPrevious($newHead);
        }
        return new self($newHead, $this->length + 1);
    }
    public function append(mixed $value): self
    {

        $newNode = new DoublyLinkedListNode($value);

        if ($this->head === null) {
            return new self($newNode, 1);
        }

        $head = $this->cloneNodes();
        $current = $head;

        while ($current->getNext() !== null) {
            $current = $current->getNext();
        }

        $current->setNext($newNode);
        $newNode->setPrevious($current);

        return new self($head, $this->length + 1);
    }
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

        if (is_null($previousNode)) {
            throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
        }
        $newNode = new DoublyLinkedListNode($value);

        $next = $previousNode->getNext();
        $newNode->setNext($next);
        $newNode->setPrevious($previousNode);
        $previousNode->setNext($newNode);

        if ($next !== null) {
            $next->setPrevious($newNode);
        }
        /* $newNode->setNext($previousNode->getNext());
        $previousNode->setNext($newNode);


        $next = $previousNode->getNext();
        $previousNode->setNext($newNode);
        if ($next !== null) {
            $next->setPrevious($newNode);
        }
 */
        return new self($newHead, $this->length + 1);
    }
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


                $nodeToRemove = $current->getNext();

                $next = $nodeToRemove->getNext();
                $current->setNext($next);
                if ($next !== null) {
                    $next->setPrevious($current);
                }
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

        $newHead = new DoublyLinkedListNode($this->head->getValue());

        return new self($newHead, 1);
    }
    // index start from 0
    public function removeAt(int $index): self
    {

        if ($index < 0 || $index >= $this->length) {
            throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
        }


        if ($index === 0) {
            return $this->removeHead();
        }

        $head = $this->cloneNodes();
        if ($head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        $previousNode = $head;

        for ($i = 0; $i < $index - 1; $i++) {
            $previousNode = $previousNode->getNext();
        }

        $nodeToRemove = $previousNode->getNext();



        $next = $nodeToRemove->getNext();
        $previousNode->setNext($next);
        if ($next !== null) {
            $next->setPrevious($previousNode);
        }

        return new self($head, $this->length - 1);
    }
    public function removeHead(): self
    {
        $head = $this->cloneNodes();

        if ($head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        return new self(
            $head->getNext(),
            $this->length - 1
        );
    }
    public function removeTail(): self
    {
        $head = $this->cloneNodes();
        if ($head === null) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }

        if ($head->getNext() === null) {
            return new self(null, 0);
        }
        $head = $this->cloneNodes();

        $previousNode = $head;

        while ($previousNode->getNext()->getNext() !== null) {
            $previousNode = $previousNode->getNext();
        }

        $previousNode->setNext(null);

        return new self($head, $this->length - 1);
    }

    public function get(int $index): DoublyLinkedListNode
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
    public function getTail(): DoublyLinkedListNode
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
    public function contains(mixed $value): DoublyLinkedListNode
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
        $head = $this->cloneNodes();
        $current = $head;
        $previous = null;
        if (is_null($current)) {
            throw new InvalidArgumentException(ErrorMessages::LINKEDLIST_IS_EMPTY);
        }
        while ($current !== null) {
            $next = $current->getNext();
            $current->setNext($previous);
            if (!is_null($previous)) {
                $previous->setPrevious($current);
            }
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
