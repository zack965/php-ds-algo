<?php


namespace Zack\PhpDsAlgo\DataStructure\Graph;

class GraphNode
{
    public function __construct(
        public mixed $value
    ) {}
    public function getValue(): mixed
    {
        return $this->value;
    }
}
