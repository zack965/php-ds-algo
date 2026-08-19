<?php


namespace Zack\PhpDsAlgo\Contracts;

use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueueNode;

interface IPriorityQueue
{
    /**
     * @param list<PriorityQueueNode> $nodes
     */
    public function insertMany(array $nodes): void;
    public function insert(mixed $value, int|float $priority): void;

    public function peek(): mixed;

    public function extract(): mixed;

    public function isEmpty(): bool;

    public function size(): int;

    public function clear(): void;
}
