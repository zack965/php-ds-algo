<?php

namespace Tests\Unit\DataStructure\Stack;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Stack\ArrayStack;

class ArrayStackTest extends TestCase
{
    // --- Factories ---

    public function testEmptyCreatesEmptyStack(): void
    {
        $stack = ArrayStack::empty();

        $this->assertTrue($stack->isEmpty());
        $this->assertSame(0, $stack->count());
        $this->assertSame([], $stack->toArray());
    }

    public function testOfCreatesStackFromValues(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $this->assertSame(3, $stack->count());
        $this->assertSame([1, 2, 3], $stack->toArray());
        $this->assertSame(3, $stack->peek());
    }

    public function testOfWithNoValuesReturnsEmptyStack(): void
    {
        $stack = ArrayStack::of();

        $this->assertTrue($stack->isEmpty());
    }

    public function testFromArrayCreatesStackFromArray(): void
    {
        $stack = ArrayStack::fromArray([1, 2, 3]);

        $this->assertSame([1, 2, 3], $stack->toArray());
        $this->assertSame(3, $stack->count());
    }

    public function testFromArrayReindexesKeys(): void
    {
        $stack = ArrayStack::fromArray([5 => 'a', 9 => 'b']);

        $this->assertSame(['a', 'b'], $stack->toArray());
    }

    public function testFromArrayWithEmptyArrayReturnsEmptyStack(): void
    {
        $stack = ArrayStack::fromArray([]);

        $this->assertTrue($stack->isEmpty());
    }

    public function testFromIterableBuildsStackFromArray(): void
    {
        $stack = ArrayStack::fromIterable([1, 2, 3]);

        $this->assertSame([1, 2, 3], $stack->toArray());
    }

    public function testFromIterableBuildsStackFromGenerator(): void
    {
        $generator = (function () {
            yield 1;
            yield 2;
            yield 3;
        })();

        $stack = ArrayStack::fromIterable($generator);

        $this->assertSame(3, $stack->count());
        $this->assertSame([1, 2, 3], $stack->toArray());
    }

    public function testFromIterableWithEmptyIterableReturnsEmptyStack(): void
    {
        $stack = ArrayStack::fromIterable([]);

        $this->assertTrue($stack->isEmpty());
    }

    // --- Iteration ---

    public function testIteratesFromTopToBottom(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $values = [];
        foreach ($stack as $value) {
            $values[] = $value;
        }

        $this->assertSame([3, 2, 1], $values);
    }

    // --- push ---

    public function testPushAddsValueToTop(): void
    {
        $stack = ArrayStack::of(1, 2);

        $result = $stack->push(3);

        $this->assertSame(3, $result->peek());
        $this->assertSame([1, 2, 3], $result->toArray());
    }

    public function testPushOnEmptyStackCreatesSingleElementStack(): void
    {
        $stack = ArrayStack::empty();

        $stack->push(1);

        $this->assertSame([1], $stack->toArray());
        $this->assertSame(1, $stack->count());
    }

    public function testPushReturnsSameInstance(): void
    {
        $stack = ArrayStack::of(1);

        $result = $stack->push(2);

        $this->assertSame($stack, $result);
    }

    // --- pop ---

    public function testPopRemovesAndReturnsTopValue(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $value = $stack->pop();

        $this->assertSame(3, $value);
        $this->assertSame([1, 2], $stack->toArray());
        $this->assertSame(2, $stack->count());
    }

    public function testPopOnSingleElementStackEmptiesIt(): void
    {
        $stack = ArrayStack::of(1);

        $value = $stack->pop();

        $this->assertSame(1, $value);
        $this->assertTrue($stack->isEmpty());
    }

    public function testPopThrowsWhenStackEmpty(): void
    {
        $stack = ArrayStack::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The stack is already empty');

        $stack->pop();
    }

    // --- peek ---

    public function testPeekReturnsTopValueWithoutRemovingIt(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $value = $stack->peek();

        $this->assertSame(3, $value);
        $this->assertSame(3, $stack->count());
    }

    public function testPeekThrowsWhenStackEmpty(): void
    {
        $stack = ArrayStack::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The stack is already empty');

        $stack->peek();
    }

    // --- bottom ---

    public function testBottomReturnsBottomValue(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $this->assertSame(1, $stack->bottom());
    }

    public function testBottomThrowsWhenStackEmpty(): void
    {
        $stack = ArrayStack::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The stack is already empty');

        $stack->bottom();
    }

    // --- isEmpty ---

    public function testIsEmptyReturnsTrueForEmptyStack(): void
    {
        $this->assertTrue(ArrayStack::empty()->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyStack(): void
    {
        $this->assertFalse(ArrayStack::of(1)->isEmpty());
    }

    // --- contains ---

    public function testContainsReturnsTrueWhenValuePresent(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $this->assertTrue($stack->contains(2));
    }

    public function testContainsReturnsFalseWhenValueAbsent(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $this->assertFalse($stack->contains(99));
    }

    public function testContainsUsesStrictComparison(): void
    {
        $stack = ArrayStack::of('1', 2, 3);

        $this->assertFalse($stack->contains(1));
    }

    public function testContainsOnEmptyStackReturnsFalse(): void
    {
        $this->assertFalse(ArrayStack::empty()->contains(1));
    }

    // --- clear ---

    public function testClearEmptiesTheStack(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $result = $stack->clear();

        $this->assertTrue($result->isEmpty());
        $this->assertSame(0, $result->count());
    }

    public function testClearReturnsSameInstance(): void
    {
        $stack = ArrayStack::of(1, 2, 3);

        $result = $stack->clear();

        $this->assertSame($stack, $result);
    }

    public function testClearOnAlreadyEmptyStackStaysEmpty(): void
    {
        $stack = ArrayStack::empty();

        $result = $stack->clear();

        $this->assertTrue($result->isEmpty());
    }

    // --- toArray ---

    public function testToArrayReturnsBottomToTopOrder(): void
    {
        $stack = ArrayStack::empty();
        $stack->push(1)->push(2)->push(3);

        $this->assertSame([1, 2, 3], $stack->toArray());
    }

    public function testToArrayOnEmptyStackReturnsEmptyArray(): void
    {
        $this->assertSame([], ArrayStack::empty()->toArray());
    }

    // --- count ---

    public function testCountReturnsNumberOfElements(): void
    {
        $stack = ArrayStack::of(1, 2, 3, 4);

        $this->assertSame(4, $stack->count());
    }

    public function testCountOnEmptyStackReturnsZero(): void
    {
        $this->assertSame(0, ArrayStack::empty()->count());
    }
}
