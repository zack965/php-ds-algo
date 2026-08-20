<?php


namespace Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm;

class DijkstraAlgorithmDistance
{
    public function __construct(
        private bool $isVisited = false,
        private mixed $value = null,
        private int|float $shortestDistance = INF,
        private mixed $previousNode = null,
    ) {}

    public function isVisited(): bool
    {
        return $this->isVisited;
    }

    public function setIsVisited(bool $isVisited): void
    {
        $this->isVisited = $isVisited;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getShortestDistance(): int|float
    {
        return $this->shortestDistance;
    }

    public function setShortestDistance(int|float $shortestDistance): void
    {
        $this->shortestDistance = $shortestDistance;
    }

    public function getPreviousNode(): mixed
    {
        return $this->previousNode;
    }

    public function setPreviousNode(mixed $previousNode): void
    {
        $this->previousNode = $previousNode;
    }
}
