<?php

declare(strict_types=1);

namespace Zack\PhpDsAlgo\DataStructure\LinkedList\Single;


class SingleLinkedListNode
{
    public function __construct(
        private mixed $value,
        private ?SingleLinkedListNode $next = null
    ) {}
    public function getValue(): mixed
    {
        return $this->value;
    }
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getNext(): ?SingleLinkedListNode
    {
        return $this->next;
    }

    public function setNext(?SingleLinkedListNode $next): void
    {
        $this->next = $next;
    }
    public function __toString(): string
    {
        $next = $this->next?->getValue();
        return sprintf(
            'Node(value=%s, next=%s)',
            var_export($this->value, true),
            var_export($next, true)
        );
    }
}
