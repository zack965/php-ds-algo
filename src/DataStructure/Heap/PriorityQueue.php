<?php


namespace Zack\PhpDsAlgo\DataStructure\Heap;

use Zack\PhpDsAlgo\Contracts\IHeap;
use Zack\PhpDsAlgo\Contracts\IPriorityQueue;
use Zack\PhpDsAlgo\enums\PriorityQueueTypeEnum;

class PriorityQueue implements IPriorityQueue
{
    private MaxHeap $maxHeap;
    private MinHeap $minHeap;
    private function getHeap(): IHeap
    {
        return match ($this->type) {
            PriorityQueueTypeEnum::Min => $this->minHeap,
            PriorityQueueTypeEnum::Max => $this->maxHeap,
        };
    }
    public function __construct(
        private readonly PriorityQueueTypeEnum $type,
        int $capacity = 10
    ) {
        if ($type === PriorityQueueTypeEnum::Min) {
            $this->minHeap = new MinHeap(
                [],
                $capacity,
                $this->compareNodesForMinHeap()
            );

            return;
        }

        $this->maxHeap = new MaxHeap(
            [],
            $capacity,
            $this->compareNodesForMaxHeap()
        );
    }

    private function compareNodesForMaxHeap(): callable
    {
        return static function (
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

    private function compareNodesForMinHeap(): callable
    {
        return static function (
            PriorityQueueNode $a,
            PriorityQueueNode $b
        ): int {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }

            return $a->getPriority() < $b->getPriority()
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
        $this->getHeap()->insert(
            new PriorityQueueNode($value, $priority)
        );
    }
    public function peek(): mixed
    {
        return $this->getHeap()->peek();
    }

    public function extract(): mixed
    {
        return $this->getHeap()->extract();
    }

    public function isEmpty(): bool
    {
        return $this->getHeap()->isEmpty();
    }

    public function size(): int
    {
        return $this->getHeap()->size();
    }

    public function clear(): void
    {
        $this->getHeap()->clear();
    }
}
