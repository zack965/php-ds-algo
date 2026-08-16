<?php


namespace Zack\PhpDsAlgo\DataStructure\Heap;

use Zack\PhpDsAlgo\Contracts\IHeap;

class MaxHeap implements IHeap
{
    /**
     * @var int[]
     */
    protected array $data = [];


    public function __construct(array $data)
    {
        $this->data = $data;
    }




    public function insert(mixed $value): void {}

    public function peek(): mixed
    {
        return "jelo";
    }

    public function extract(): mixed
    {
        return "jelo";
    }

    public function isEmpty(): bool
    {
        return count($this->data) == 0;
    }

    public function size(): int
    {
        return count($this->data);
    }
}
