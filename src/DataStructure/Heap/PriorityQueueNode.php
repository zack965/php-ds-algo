<?php


namespace Zack\PhpDsAlgo\DataStructure\Heap;

class PriorityQueueNode
{
    public function __construct(
        private mixed $value,
        private int|float $priority
    ) {}

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getPriority(): int|float
    {
        return $this->priority;
    }
}
