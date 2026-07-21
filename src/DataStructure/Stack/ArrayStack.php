<?php

namespace Zack\PhpDsAlgo\DataStructure\Stack;

use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use Zack\PhpDsAlgo\Contracts\IStack;

class ArrayStack implements IStack, IteratorAggregate
{
    public function __construct(private array $items = [])
    {
        $this->items = array_values($items);
    }


    /**
     * getIterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        yield from array_reverse($this->items);
    }
    /**
     * Create an empty stack.
     */
    public static function empty(): static
    {
        return new static();
    }

    /**
     * Create a stack from one or more values.
     */
    public static function of(mixed ...$values): static
    {
        return new static($values);
    }

    /**
     * Create a stack from an array.
     */
    public static function fromArray(array $values): static
    {
        return new static($values);
    }

    /**
     * Create a stack from any iterable.
     */
    public static function fromIterable(iterable $values): static
    {
        if (is_array($values)) {
            return new static($values);
        }

        return new static(iterator_to_array($values, false));
    }


    /**
     * Push an element onto the top of the stack.
     */
    public function push(mixed $value): static
    {
        $this->items[] = $value;
        return $this;
    }

    /**
     * Remove and return the top element.
     *
     * @throws InvalidArgumentException
     */
    public function pop(): mixed
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException("The stack is already empty");
        }
        return  array_pop($this->items);
    }

    /**
     * Return the top element without removing it.
     *
     * @throws InvalidArgumentException
     */
    public function peek(): mixed
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException("The stack is already empty");
        }
        return $this->items[count($this->items) - 1];
    }

    /**
     * Return the bottom element.
     *
     * @throws InvalidArgumentException
     */
    public function bottom(): mixed
    {
        if (empty($this->items)) {
            throw new InvalidArgumentException("The stack is already empty");
        }
        return $this->items[0];
    }

    /**
     * Determine whether the stack is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Determine whether the stack contains a value.
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }


    /**
     * Remove all elements.
     */
    public function clear(): static
    {
        $this->items = [];
        return $this;
    }
    /**
     * Return the stack as an array.
     *
     * The first element is the bottom of the stack,
     * and the last element is the top.
     *
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }
    /**
     * Number of elements.
     */
    public function count(): int
    {
        return count($this->items);
    }
}
