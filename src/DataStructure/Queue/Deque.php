<?php


namespace Zack\PhpDsAlgo\DataStructure\Queue;

use Zack\PhpDsAlgo\Contracts\IDeque;

class Deque extends Queue  implements IDeque
{
    public function enqueueFront(mixed $item): static
    {
        array_unshift($this->items, $item);
        return $this;
    }

    public function dequeueTail(): mixed
    {
        return array_pop($this->items);
    }
}
