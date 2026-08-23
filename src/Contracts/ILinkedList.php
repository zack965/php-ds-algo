<?php

namespace Zack\PhpDsAlgo\Contracts;

use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedListNode;
// This is for single linked list
interface ILinkedList
{
    // Meta
    public function getLength(): int;
    public function getHead(): ?SingleLinkedListNode;

    // Iterator
    public function getIterator(): \Traversable;

    // Creation (static methods cannot be enforced in interface in PHP)
    // so we only document them here, not enforce
    public static function empty(): self;

    // Insertions
    public function prepend(mixed $value): self;
    public function append(mixed $value): self;
    public function insert(mixed $value, int $index): self;
    public function insertBeforeNode(mixed $target, mixed $value): self;
    public function insertAfterNode(mixed $target, mixed $value): self;

    // Removal
    public function removeByValue(mixed $value): self;
    public function removeAt(int $index): self;
    public function removeHead(): self;
    public function removeTail(): self;
    public function clear(): self;
    public function clearAndKeepHead(): self;

    // Access
    public function get(int $index): SingleLinkedListNode;
    public function getTail(): SingleLinkedListNode;
    public function contains(mixed $value): SingleLinkedListNode;
    public function indexOf(mixed $value): int;

    // Transformations
    public function reverse(): self;
    public function toArray(): array;
    public function toArrayValues(): array;

    // Functional
    public function map(callable $fn): self;
    public function filter(callable $fn): self;
    public function reduce(callable $fn, mixed $initial = null): mixed;
}
