<?php


namespace Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly;

use IteratorAggregate;

class DoublyLinkedList implements IteratorAggregate
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
}
