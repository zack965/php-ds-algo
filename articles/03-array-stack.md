# ArrayStack: a mutable, array-backed LIFO stack

`Zack\PhpDsAlgo\DataStructure\Stack\ArrayStack` is deliberately the odd one
out next to the linked lists: instead of the persistent/immutable pattern,
it's a plain mutable object wrapping a PHP array, because that's the
conventional shape a stack API is expected to have (`push()` returns `$this`
for chaining, not a new stack). It implements `IStack` and
`IteratorAggregate`.

## Internal representation

```php
public function __construct(private array $items = [])
{
    $this->items = array_values($items);
}
```

`$items` is a normal zero-indexed PHP array. The **end** of the array
(`$items[count($items) - 1]`) is the **top** of the stack — i.e. `push`
appends, `pop` pops from the end, both O(1) amortized PHP array operations.
`array_values()` in the constructor strips any non-sequential keys a caller
might pass in, guaranteeing the internal array is always a clean list.

## Construction

Four static factories, all funneling through the constructor:

- `ArrayStack::empty()`
- `ArrayStack::of(1, 2, 3)` — variadic
- `ArrayStack::fromArray([1, 2, 3])`
- `ArrayStack::fromIterable($iterable)` — handles both arrays (fast path)
  and any other `iterable` (via `iterator_to_array($values, false)`, which
  discards keys so the result stays a clean list)

## Core operations

```php
public function push(mixed $value): static
{
    $this->items[] = $value;
    return $this;
}

public function pop(): mixed
{
    if (empty($this->items)) {
        throw new InvalidArgumentException("The stack is already empty");
    }
    return array_pop($this->items);
}
```

`push()` returning `static` (i.e. `$this`) enables fluent chaining:
`ArrayStack::empty()->push(1)->push(2)->push(3)`. `pop()` and `peek()` both
throw `InvalidArgumentException` on empty rather than returning `null` — the
caller is expected to check `isEmpty()` first, or catch. `peek()` reads the
last element without removing it; `bottom()` reads `$items[0]` — the
oldest-pushed element still present.

`isEmpty()`, `contains($value)` (strict `in_array` with `true` for the third
argument, so type-safe equality), `clear()` (resets to `[]`, returns
`$this`), `toArray()` (bottom-to-top order, i.e. the raw internal array),
and `count()` round out the surface.

## Iteration order is reversed on purpose

```php
public function getIterator(): Traversable
{
    yield from array_reverse($this->items);
}
```

`foreach ($stack as $item)` yields **top-to-bottom** — the most naturally
useful order for a stack (you almost always want to inspect "what's next to
pop" first). This is the opposite order from `toArray()`, which returns
bottom-to-top (the raw storage order) — worth remembering, since the two
don't agree.

## Complexity summary

| Operation | Time |
|---|---|
| `push` | O(1) amortized |
| `pop` | O(1) |
| `peek` / `bottom` | O(1) |
| `isEmpty` / `count` | O(1) |
| `contains` | O(n) |
| `clear` | O(1) |
| `toArray` | O(1) (returns internal array directly, no copy loop) |
| iteration (`foreach`) | O(n), reversed via `array_reverse` up front |

## Why mutable here but immutable for linked lists?

Both are valid, documented choices in this codebase rather than an
inconsistency — see [`00-overview.md`](00-overview.md) and
[`01-single-linked-list.md`](01-single-linked-list.md). Stacks/queues follow
the conventional mutable-container API most PHP consumers expect
(`push`/`pop` changing the object they're called on); linked lists follow
the persistent-data-structure pattern instead. `Queue` (next article) makes
the same mutable choice for the same reason.
