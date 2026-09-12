# Queue & Deque: a mutable, array-backed FIFO queue, plus its double-ended sibling

`Zack\PhpDsAlgo\DataStructure\Queue\Queue` implements `IQueue` the same way
`ArrayStack` implements `IStack`: a mutable object wrapping a plain PHP
array, front-to-rear ordering, no persistence. `Deque` extends it, adding
push/pop at the front too.

## Internal representation

```php
public function __construct(
    protected array $items = [],
    private int $maxCapacity = PHP_INT_MAX,
) {
    if ($this->maxCapacity < count($this->items)) {
        throw new InvalidArgumentException(
            'Maximum capacity cannot be smaller than the number of initial items.'
        );
    }
}
```

`$items[0]` is the **front** (next to be dequeued); the last element is the
**rear**. `enqueue()` appends to the end, `dequeue()` removes from the front
with `array_shift()`.

```php
public function enqueue(mixed $item): static
{
    if ($this->isFull()) {
        throw new InvalidArgumentException('The queue is full.');
    }
    $this->items[] = $item;
    return $this;
}

public function dequeue(): mixed
{
    if (count($this->items) == 0) {
        throw new InvalidArgumentException("The Queue is empty");
    }
    return array_shift($this->items);
}
```

`isFull()` compares `count($this->items) >= $this->maxCapacity`, and
`enqueue()` delegates straight to it — so the queue never actually holds
more than `maxCapacity` items; `enqueue()` throws exactly once the count
would reach the cap, with no off-by-one slack either way.

## `maxCapacity` defaults to unbounded — set it explicitly for a real cap

Unlike `ArrayStack`, `Queue` has **no static factory methods** (`empty()`,
`of()`, etc.) — the only entry point is `new Queue($items, $maxCapacity)`.
`maxCapacity` defaults to `PHP_INT_MAX`, so a plain `new Queue($items)`
behaves as an effectively unbounded queue out of the box:

```php
$queue = new Queue([]);
$queue->enqueue('a'); // fine — no capacity set, defaults to PHP_INT_MAX

$queue = new Queue([], 2);          // capacity set from the start
$queue->setMaxCapacity(10);          // or changed later
```

Both the constructor and `setMaxCapacity()` validate against the queue's
current size: the constructor throws `InvalidArgumentException` if
`maxCapacity` is smaller than the number of initial `$items`, and
`setMaxCapacity()` throws the same way if the new capacity is smaller than
`count()` right now. Either way, the invariant "the queue never holds more
items than its capacity" can't be violated from outside the class.

This is a change from an earlier version of `Queue`, where `maxCapacity` was
a typed property with no default at all — reading it (via `enqueue()`,
`isFull()`, or `getMaxCapacity()`) before an explicit `setMaxCapacity()`
call threw a PHP `Error` ("must not be accessed before initialization"),
not a caught `InvalidArgumentException`. That mattered beyond `Queue`
itself: `AbstractTree`, `BinaryTree`, and `BinarySearchTree` (see
[`16-binary-search-tree.md`](16-binary-search-tree.md)) all build a bare
`new Queue()` internally for breadth-first traversals and just call
`enqueue()` on it without ever setting a capacity — every one of those call
sites crashed under the old default. Giving `Queue` a real default fixed
all of them at once, rather than patching each call site individually.

## Everything else

`front()` / `rear()` read `$items[0]` / `$items[count($items) - 1]`
directly with no empty-check — calling either on an empty queue triggers a
PHP "undefined array key" warning and returns `null`, rather than throwing
`InvalidArgumentException` the way `dequeue()`/`ArrayStack::peek()` do. This
is inconsistent with the rest of the library's "throw on invalid state"
convention and worth treating as a known gotcha rather than relying on it.

`isEmpty()`, `contains($item)` (strict `in_array`), `clear()`, `toArray()`
(front-to-rear), `toIterable()` (a generator yielding front-to-rear, useful
for lazily consuming a large queue without materializing an array), and
`count()` (via `Countable`, so `count($queue)` works directly) round out the
surface. Unlike `ArrayStack`, `Queue` does **not** implement
`IteratorAggregate` — there's no `foreach ($queue as ...)`; use
`toIterable()` or `toArray()` instead.

## Deque: push/pop at both ends

`Zack\PhpDsAlgo\DataStructure\Queue\Deque` implements `IDeque` (`extends
IQueue`) and extends `Queue` directly, adding exactly two methods — the
mirror image of the pair `Queue` already has:

```php
class Deque extends Queue implements IDeque
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
```

Everything else — `enqueue()`/`dequeue()`, capacity, `front()`/`rear()`,
`toArray()`, and so on — is inherited unchanged from `Queue`. Two
asymmetries are worth knowing about the two new methods specifically:

- **`enqueueFront()` doesn't check `isFull()`.** Inherited `enqueue()`
  guards against `maxCapacity`; `enqueueFront()` calls `array_unshift()`
  directly and can push the deque past its configured capacity.
- **`dequeueTail()` doesn't throw on an empty deque.** Inherited
  `dequeue()` throws `InvalidArgumentException` on empty; `dequeueTail()`
  just returns whatever `array_pop()` returns on an empty array — `null`.

Both are a direct consequence of `enqueueFront()`/`dequeueTail()` calling
the underlying PHP array functions directly rather than routing through
`Queue`'s own `enqueue()`/`dequeue()` (which is where those checks live) —
not a deliberately different contract for the front/rear-adjacent
operations, just something to know before relying on the symmetry.

## Complexity summary

| Operation | Time | Notes |
|---|---|---|
| `enqueue` | O(1) amortized | throws once `isFull()` |
| `dequeue` | O(n) | `array_shift()` re-indexes the whole array |
| `enqueueFront` (`Deque`) | O(n) | `array_unshift()` re-indexes the whole array; no capacity check |
| `dequeueTail` (`Deque`) | O(1) | `array_pop()`; returns `null` instead of throwing on empty |
| `front` / `rear` | O(1) | no empty-state guard (see above) |
| `isEmpty` / `count` / `isFull` | O(1) | |
| `contains` | O(n) | |
| `clear` | O(1) | |
| `toArray` | O(1) | returns internal array directly |
| `toIterable` | O(1) to start, O(n) to exhaust | generator |

`dequeue()`'s O(n) cost (from `array_shift()` reindexing every remaining
element) — and `enqueueFront()`'s equivalent cost from `array_unshift()` —
is the one real performance caveat here — a circular-buffer or
linked-list-backed queue would make both ends O(1), and is exactly the
kind of alternative implementation flagged as a possible future addition in
this project's backlog docs (`TODO.md`), not something currently in `src/`.
