<?php


namespace Zack\PhpDsAlgo\Contracts;

interface IDeque extends IQueue
{
    public function enqueueFront(mixed $item): static;

    public function dequeueTail(): mixed;
}
