<?php


namespace Zack\PhpDsAlgo\Contracts;

interface IHeap
{
    public function insert(mixed $value): void;

    public function peek(): mixed;

    public function extract(): mixed;

    public function isEmpty(): bool;

    public function size(): int;
}
