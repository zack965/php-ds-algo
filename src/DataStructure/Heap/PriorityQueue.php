<?php


namespace Zack\PhpDsAlgo\DataStructure\Heap;

use Zack\PhpDsAlgo\Contracts\IPriorityQueue;

class PriorityQueue implements IPriorityQueue
{
    private MaxHeap $maxHeap;


    public function __construct(
        int $capacity = 10
    ) {
        $this->maxHeap = new MaxHeap(
            [],
            $capacity,
            $this->compareNodes()
        );
    }
    private function compareNodes(): callable
    {
        return function (
            PriorityQueueNode $a,
            PriorityQueueNode $b
        ): int {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }

            return $a->getPriority() > $b->getPriority()
                ? -1
                : 1;
        };
    }
    public function insertMany(array $nodes): void
    {
        foreach ($nodes as $node) {
            $this->insert($node->getValue(), $node->getPriority());
        }
    }

    public function insert(mixed $value, int|float $priority): void
    {
        $node = new PriorityQueueNode($value, $priority);
        $this->maxHeap->insert($node);
    }
    public function peek(): mixed
    {
        return $this->maxHeap->peek();
    }
    public function extract(): mixed
    {
        return $this->maxHeap->extract();
    }

    public function isEmpty(): bool
    {
        return $this->maxHeap->isEmpty();
    }

    public function size(): int
    {
        return $this->maxHeap->size();
    }

    public function clear(): void
    {
        $this->maxHeap->clear();
    }
}
