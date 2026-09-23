# ArrayStack

**Namespace:** `Zack\PhpDsAlgo\DataStructure\Stack`
**Class:** `ArrayStack`
**Contract:** `Zack\PhpDsAlgo\Contracts\IStack`

## What it is

A **stack** is a Last-In, First-Out (LIFO) collection. You add items to the
**top** and remove them from the top, so the most recently added item always
comes out first. Think of a stack of plates.

`ArrayStack` stores its items in a PHP array. The end of the array is the top
of the stack, so `push` and `pop` are both constant-time. It is **mutable**,
and `push()` returns the stack itself, so calls can be chained.

```php
use Zack\PhpDsAlgo\DataStructure\Stack\ArrayStack;

$stack = ArrayStack::empty()->push(1)->push(2)->push(3);

$stack->peek(); // 3
$stack->pop();  // 3
$stack->pop();  // 2
$stack->count(); // 1
```

## Creating a stack

```php
ArrayStack::empty();
ArrayStack::of(1, 2, 3);          // variadic
ArrayStack::fromArray([1, 2, 3]);
ArrayStack::fromIterable($iterable);
new ArrayStack([1, 2, 3]);
```

The last value you pass ends up on top. Input keys are normalized, so the
stack always stores a clean, zero-indexed list.

## API

```php
$stack->push($value);    // add to top, returns $this
$stack->pop();           // remove and return the top
$stack->peek();          // read the top without removing it
$stack->bottom();        // read the oldest item still in the stack
$stack->isEmpty();
$stack->count();         // also works with count($stack)
$stack->contains($value); // strict (===) comparison
$stack->clear();         // returns $this
$stack->toArray();       // bottom → top
```

`pop()` and `peek()` throw `InvalidArgumentException` when the stack is
empty. Check `isEmpty()` first if the stack might be empty.

### Iteration order

`foreach` yields items **top to bottom**, the order they would be popped in:

```php
$stack = ArrayStack::of('a', 'b', 'c');

foreach ($stack as $item) {
    echo $item; // c, b, a
}

$stack->toArray(); // ['a', 'b', 'c'] (storage order, bottom → top)
```

## Complexity

| Operation | Time |
|---|---|
| `push` | O(1) amortized |
| `pop` | O(1) |
| `peek` / `bottom` | O(1) |
| `isEmpty` / `count` | O(1) |
| `clear` | O(1) |
| `toArray` | O(1) |
| `contains` | O(n) |
| `foreach` | O(n) |

Space: O(n).

## When to use it

- **Undo/redo:** push each action, and pop to revert it.
- **Backtracking:** maze solving, puzzle solvers, and iterative depth-first
  search.
- **Parsing:** matching brackets, evaluating expressions, converting infix
  to postfix.
- **Replacing recursion** with an explicit stack for deep structures.
- **Reversing** a sequence.

## When to choose something else

- **Processing items in arrival order:** use `Queue` (FIFO).
- **Adding and removing at both ends:** use `Deque`.
- **Always taking the smallest or highest-priority item:** use `MinHeap`,
  `MaxHeap` or `PriorityQueue`.
- **Keeping every past version:** use the persistent `SingleLinkedList`, where
  prepending and removing the head are also O(1).
