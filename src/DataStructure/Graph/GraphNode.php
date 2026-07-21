<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

class GraphNode
{
    public function __construct(
        public readonly int|string $value
    ) {}
    public function getValue(): mixed
    {
        return $this->value;
    }
}
