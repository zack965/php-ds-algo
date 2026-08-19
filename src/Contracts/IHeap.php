<?php


namespace Zack\PhpDsAlgo\Contracts;

interface IHeap
{



    public function insert(mixed $value): void;
    public function peek(): mixed;
    public function extract(): mixed;
    public function isEmpty(): bool;
    public function size(): int;
    public function heapifyUp(int $index): void;
    public function heapifyDown(int $index): void;
    public function buildHeap(): void;
    public function clear(): void;
    public function isValid(): bool;

    // New methods for capacity management
    public function getCapacity(): int;
    public function ensureCapacity(int $minCapacity): void;
}
