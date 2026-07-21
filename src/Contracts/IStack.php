<?php


namespace Zack\PhpDsAlgo\Contracts;

use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

interface IStack extends IteratorAggregate
{

    public function getIterator(): Traversable;

    /**
     * Push an element onto the top of the stack.
     */
    public function push(mixed $value): static;

    /**
     * Remove and return the top element.
     *
     * @throws InvalidArgumentException;

     */
    public function pop(): mixed;

    /**
     * Return the top element without removing it.
     *
     * @throws InvalidArgumentException;

     */
    public function peek(): mixed;

    /**
     * Return the bottom element.
     *
     * @throws InvalidArgumentException
     */
    public function bottom(): mixed;

    /**
     * Determine whether the stack is empty.
     */
    public function isEmpty(): bool;

    /**
     * Determine whether the stack contains a value.
     */
    public function contains(mixed $value): bool;


    /**
     * Remove all elements.
     */
    public function clear(): static;

    /**
     * Return the stack as an array.
     *
     * The first element is the bottom of the stack,
     * and the last element is the top.
     *
     * @return array<int, mixed>
     */
    public function toArray(): array;
    /**
     * Create an empty stack.
     */
    public static function empty(): static;

    /**
     * Create a stack from one or more values.
     */
    public static function of(mixed ...$values): static;

    /**
     * Create a stack from an array.
     *
     * @param array<int, mixed> $values
     */
    public static function fromArray(array $values): static;

    /**
     * Create a stack from any iterable.
     *
     * @param iterable<mixed> $values
     */
    public static function fromIterable(iterable $values): static;
}
