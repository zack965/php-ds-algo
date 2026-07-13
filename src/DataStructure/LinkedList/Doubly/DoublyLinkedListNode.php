<?php


namespace Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly;

class DoublyLinkedListNode
{
    public function __construct(
        private mixed $value,
        private ?DoublyLinkedListNode $next = null,
        private ?DoublyLinkedListNode $previous = null
    ) {}
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getNext(): ?DoublyLinkedListNode
    {
        return $this->next;
    }

    public function setNext(?DoublyLinkedListNode $next): void
    {
        $this->next = $next;
    }


    public function getPrevious(): ?DoublyLinkedListNode
    {
        return $this->previous;
    }

    public function setPrevious(DoublyLinkedListNode $previous): void
    {
        $this->previous = $previous;
    }
    public function __toString(): string
    {
        $prev = $this->previous?->getValue();
        $next = $this->next?->getValue();

        return sprintf(
            'Node(value=%s, previous=%s, next=%s)',
            var_export($this->value, true),
            var_export($prev, true),
            var_export($next, true)
        );
    }
}
