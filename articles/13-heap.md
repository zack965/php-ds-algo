# Heap: MinHeap, MaxHeap & PriorityQueue — an array-backed binary heap with pluggable ordering

`Zack\PhpDsAlgo\DataStructure\Heap\MinHeap` and `...\MaxHeap` are the
library's binary heap structures: an array-backed binary tree, with
`MinHeap` keeping the smallest element at the root and `MaxHeap` the
largest. Both extend a shared `AbstractBinaryHeap` base class and implement
the `IHeap` contract. Like `ArrayStack`, `Queue`, and `Graph` (and unlike the
linked lists), a heap is **mutable** — `insert()`, `extract()`, and
`clear()` change the object in place. The same namespace also has
`PriorityQueue`/`PriorityQueueNode` — a value/priority wrapper built on top
of `MinHeap`/`MaxHeap` (see [PriorityQueue](#priorityqueue-a-valuepriority-wrapper-on-top-of-minheapmaxheap)
below), which is what you reach for when you want to queue arbitrary values
by a separate priority rather than feed the heap directly comparable
values.

## Internal representation

```php
abstract class AbstractBinaryHeap implements IHeap
{
    protected array $heap = [];
    protected int $size = 0;
    protected int $capacity = 0;
    protected $comparator;

    protected const DEFAULT_CAPACITY = 16;
    protected const GROWTH_FACTOR = 2;

    public function __construct(array $data = [], int $capacity = 0, ?callable $comparator = null)
    {
        $this->capacity = $capacity > 0 ? $capacity : self::DEFAULT_CAPACITY;
        $this->heap = array_fill(0, $this->capacity, null);
        $this->comparator = $comparator ?? $this->getDefaultComparator();

        if (!empty($data)) {
            $this->buildFromArray($data);
        }
    }
}
```

It's the classic array-as-binary-tree layout: for a node at index `$i`, its
parent is at `floor(($i - 1) / 2)`, its children at `2*$i + 1` and `2*$i + 2`
(`getParentIndex()`/`getLeftChildIndex()`/`getRightChildIndex()`). `$heap` is
pre-sized to `$capacity` with `null` filler up front — `$size` (not
`count($heap)`) is the real element count, so the class never has to reindex
or resize on every single insert.

`MinHeap` and `MaxHeap` differ in exactly two things: their `getDefaultComparator()`
(ascending vs. descending) and the direction of the comparison inside
`heapifyDown()`. Everything else — construction, capacity growth, `extract()`,
`buildHeap()` — lives once in `AbstractBinaryHeap` and is shared.

## Every comparison goes through one place: `compare()`

```php
protected function compare(mixed $a, mixed $b): int
{
    return ($this->comparator)($a, $b);
}
```

`MinHeap`'s default comparator returns `-1` when `$a < $b`, `MaxHeap`'s
returns `-1` when `$a > $b` — "returns `-1`" meaning "`$a` belongs closer to
the root." Passing a third constructor argument replaces this comparator
entirely, which is what makes both classes generic priority queues rather
than fixed to `<`/`>` on scalars (more on this below).

## `insert()`: append, then bubble up

```php
public function insert(mixed $value): void
{
    $this->ensureCapacity($this->size + 1);
    $this->heap[$this->size] = $value;
    $this->size++;
    $this->heapifyUp($this->size - 1);
}
```

The new value lands at the next free slot (`$this->heap[$this->size]`), then
`heapifyUp()` walks it toward the root, one parent swap at a time, until its
parent already belongs above it per `compare()`:

```php
public function heapifyUp(int $index): void
{
    $currentIndex = $index;
    while ($currentIndex != 0) {
        $parentIndex = $this->getParentIndex($currentIndex);
        if ($this->compare($this->heap[$currentIndex], $this->heap[$parentIndex]) >= 0) {
            break;
        }
        AlgorythmesGlobalHelpers::swapValuesOfArray($this->heap, $currentIndex, $parentIndex);
        $currentIndex = $parentIndex;
    }
}
```

`insert()` is `O(log n)` — at most one swap per tree level.

## `extract()`: swap root with the last leaf, then sink down

```php
public function extract(): mixed
{
    if ($this->isEmpty()) {
        throw new RuntimeException("Heap is empty");
    }

    $root = $this->heap[0];
    $lastElement = $this->heap[$this->size - 1];

    $this->heap[0] = $lastElement;
    $this->heap[$this->size - 1] = null;
    $this->size--;

    if ($this->size > 0) {
        $this->heapifyDown(0);
    }

    return $root;
}
```

The root is always the answer (min for `MinHeap`, max for `MaxHeap`).
Removing it the naive way (shifting everything) would be `O(n)`; instead the
*last* element is moved into the now-empty root slot and sunk back down to
where it belongs — `heapifyDown()` repeatedly swaps it with whichever child
"wins" per `compare()` until neither child outranks it:

```php
public function heapifyDown(int $index): void
{
    $currentIndex = $index;
    while ($this->hasLeftChild($currentIndex)) {
        $leftChild = $this->getLeftChildIndex($currentIndex);
        $rightChild = $this->getRightChildIndex($currentIndex);

        if (!$this->hasRightChild($currentIndex)) {
            $smallestChildIndex = $leftChild;
        } else {
            $smallestChildIndex = $this->compare($this->heap[$leftChild], $this->heap[$rightChild]) === -1
                ? $leftChild
                : $rightChild;
        }

        if ($this->compare($this->heap[$currentIndex], $this->heap[$smallestChildIndex]) <= 0) {
            break;
        }
        AlgorythmesGlobalHelpers::swapValuesOfArray($this->heap, $currentIndex, $smallestChildIndex);
        $currentIndex = $smallestChildIndex;
    }
}
```

(`MaxHeap::heapifyDown()` is the same shape, picking the "largest" child
instead.) Both `insert()` and `extract()` are `O(log n)`; draining the whole
heap with repeated `extract()` calls — the standard way to read a heap out in
sorted order — is `O(n log n)`, same as it is for any binary-heap-based sort.

## Building a heap from an existing array: `buildHeap()`

Passing a non-empty `$data` array to the constructor doesn't insert elements
one at a time (which would be `O(n log n)`) — it copies the array straight
into the backing store, then calls `buildHeap()`, which runs `heapifyDown()`
starting from the last **parent** node and working back to the root:

```php
public function buildHeap(): void
{
    if ($this->size <= 1) {
        return;
    }
    $parentIndex = $this->getParentIndex(count($this->heap) - 1);
    for ($start = $parentIndex; $start >= 0; $start--) {
        $this->heapifyDown($start);
    }
}
```

Skipping the leaves (they trivially satisfy the heap property alone) and
sinking every internal node exactly once is the textbook `O(n)` heapify —
cheaper than `n` individual `O(log n)` inserts.

```php
$heap = new MinHeap([5, 3, 8, 1, 9, 2, 7]); // heapified immediately, O(n)
$heap->peek(); // 1
```

## Custom comparators: the main extension point

The third constructor argument, `?callable $comparator`, is what turns
`MinHeap`/`MaxHeap` into general-purpose priority queues rather than "smallest
or largest scalar" containers. It's called as `($a, $b): int` everywhere the
class needs to compare two elements:

```php
class Task {
    public function __construct(public string $name, public int $priority) {}
}

$byPriority = fn(Task $a, Task $b) => $a->priority <=> $b->priority;
$heap = new MinHeap([], 0, $byPriority);
$heap->insert(new Task('low', 5));
$heap->insert(new Task('urgent', 1));
$heap->extract()->name; // 'urgent'
```

A `MinHeap` given a descending comparator behaves exactly like a `MaxHeap`
with the default one, and vice versa — the class you instantiate only picks
the *default* `getDefaultComparator()`, used when the third argument is
`null`. Passing a comparator overrides it completely, for both `insert()`'s
`heapifyUp()` path and `extract()`'s `heapifyDown()` path.

### The sharp edge: comparators must return exactly `-1`/`0`/`1`

Look again at the `heapifyDown()` snippet above:

```php
$smallestChildIndex = $this->compare($this->heap[$leftChild], $this->heap[$rightChild]) === -1
    ? $leftChild
    : $rightChild;
```

That's a strict `===` check against the literal integer `-1` — not "is the
result negative." `heapifyUp()`, by contrast, only ever tests the sign
(`>= 0`). The practical consequence: a comparator written in the common
`usort()`-adjacent style —

```php
fn($a, $b) => $a - $b        // or `strcmp($a, $b)`-style "any signed magnitude"
```

— works fine for `insert()`-only usage, but silently misorders the heap the
moment `extract()` or the array constructor calls `heapifyDown()`, because a
result like `-5` fails the `=== -1` check and falls through to "pick the
right child" even when the left child was the correct pick. This was caught
directly by testing: building `new MinHeap([1, 3, 2, 5, 4], 0, fn($a, $b) => $a - $b)`
and draining it does **not** come out sorted, while the same heap built with
`fn($a, $b) => $a <=> $b` does. PHP's spaceship operator is specified to
return exactly `-1`/`0`/`1`, so writing every heap comparator with `<=>`
sidesteps this entirely — treat that as the required idiom here, not just a
style preference.

## `isValid()`: an actual invariant check

```php
public function isValid(): bool
{
    if ($this->size <= 1) {
        return true;
    }
    for ($i = 0; $i < $this->size - 1; $i++) {
        if ($this->hasLeftChild($i) && $this->compare($this->heap[$i], $this->heap[$this->getLeftChildIndex($i)]) > 0) {
            return false;
        }
        if ($this->hasRightChild($i) && $this->compare($this->heap[$i], $this->heap[$this->getRightChildIndex($i)]) > 0) {
            return false;
        }
    }
    return true;
}
```

It walks every parent/child pair and confirms `compare(parent, child) <= 0`
throughout — a genuine `O(n)` invariant check, useful mostly for tests and
debugging rather than something client code calls on a hot path.

## Everything else

```php
$heap->isEmpty();          // bool
$heap->size();              // current element count
$heap->getCapacity();       // current backing-array capacity (16 by default, doubles on growth)
$heap->ensureCapacity(100); // pre-grow if not already there; called automatically by insert()
$heap->clear();             // empties in place, keeps current capacity
$heap->toArray();           // the raw level-order backing array — NOT sorted order
```

`toArray()` is a common trip-up: it returns the heap's internal array
representation (root first, then its children, then their children, ...),
not the elements in ascending/descending order. Getting a sorted array means
draining the heap with repeated `extract()` calls, which also empties it.

`peek()` and `extract()` both throw **`RuntimeException`** (`"Heap is
empty"`) on an empty heap — every other empty-container error in this
library (`ArrayStack::pop()`, `Queue::dequeue()`, the linked lists) throws
`InvalidArgumentException` instead. If you're catching by exception type
across the library generically, `Heap` is the one exception (pun intended).

`MinHeap`/`MaxHeap` have **no static factories** — no `empty()`, `of()`,
`fromArray()` the way `ArrayStack`/the linked lists do. `new MinHeap(...)` /
`new MaxHeap(...)` is the only entry point, same as `Queue`.

`heapifyUp(int $index)`, `heapifyDown(int $index)`, and `buildHeap()` are
part of the public `IHeap` contract (so they're technically callable from
outside), but they operate on raw internal array positions and are meant to
be used by the class itself — `insert()`, `extract()`, and the array
constructor already call them at exactly the right moments. There's no
reason for ordinary client code to call them directly.

## Complexity summary

| Operation | Time | Notes |
|---|---|---|
| `insert` | O(log n) amortized | O(n) worst case on the rare resize |
| `peek` | O(1) | throws `RuntimeException` if empty |
| `extract` | O(log n) | throws `RuntimeException` if empty |
| array constructor (`new MinHeap($data)`) | O(n) | `buildHeap()`, not n individual inserts |
| `isEmpty` / `size` / `getCapacity` | O(1) | |
| `ensureCapacity` | O(1) amortized, O(n) on an actual resize | copies the backing array when it grows |
| `clear` | O(capacity) | reallocates the `null`-filled backing array |
| `toArray` | O(n) | returns level-order, not sorted, order |
| `isValid` | O(n) | walks every parent/child pair once |
| drain-to-sorted (`while (!empty()) extract()`) | O(n log n) | same asymptotic cost as any binary-heap sort |

## PriorityQueue: a value/priority wrapper on top of MinHeap/MaxHeap

`Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueue` is not a third heap
variant — it's a small adapter that holds *either* a private `MinHeap` *or*
a private `MaxHeap` (never both), for the common case where you want to
queue arbitrary values by a separate numeric priority instead of feeding
the heap directly comparable values. Which one backs a given queue is fixed
at construction time by a required `PriorityQueueTypeEnum $type` argument —
there's no default and no way to change it after the fact:

```php
class PriorityQueue implements IPriorityQueue
{
    private MaxHeap $maxHeap;
    private MinHeap $minHeap;

    public function __construct(
        private readonly PriorityQueueTypeEnum $type,
        int $capacity = 10
    ) {
        if ($type === PriorityQueueTypeEnum::Min) {
            $this->minHeap = new MinHeap([], $capacity, $this->compareNodesForMinHeap());
            return;
        }
        $this->maxHeap = new MaxHeap([], $capacity, $this->compareNodesForMaxHeap());
    }

    private function getHeap(): IHeap
    {
        return match ($this->type) {
            PriorityQueueTypeEnum::Min => $this->minHeap,
            PriorityQueueTypeEnum::Max => $this->maxHeap,
        };
    }
}
```

Only the heap matching `$type` is ever constructed — a `Min` queue never
allocates a `MaxHeap` and vice versa — and every other method (`insert()`,
`peek()`, `extract()`, `isEmpty()`, `size()`, `clear()`) is written once
against `getHeap()` rather than duplicated per type.

`PriorityQueueTypeEnum` (`Zack\PhpDsAlgo\enums\PriorityQueueTypeEnum`) is a
small string-backed enum with two cases:

```php
enum PriorityQueueTypeEnum: string
{
    case Max = 'max';
    case Min = 'min';
}
```

Every element stored in the heap is a `PriorityQueueNode` — a tiny value
object pairing a `value` with an `int|float` `priority`:

```php
class PriorityQueueNode
{
    public function __construct(
        private mixed $value,
        private int|float $priority
    ) {}

    public function getValue(): mixed { return $this->value; }
    public function getPriority(): int|float { return $this->priority; }
}
```

`PriorityQueue` carries two comparators internally, one per type, and hands
the matching one to whichever heap it constructs — `compareNodesForMaxHeap()`
returns `-1` ("belongs above") when `$a`'s priority is greater;
`compareNodesForMinHeap()` returns `-1` when `$a`'s priority is smaller. Both
compare only `getPriority()` (never `getValue()`) and are written with the
`-1`/`0`/`1` convention the heap's `heapifyDown()` requires (see the sharp
edge above) — so `PriorityQueue` itself is immune to the "comparator must
return exactly `-1`/`0`/`1`" trap; you never write a comparator for it
yourself.

```php
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueue;
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueueNode;
use Zack\PhpDsAlgo\enums\PriorityQueueTypeEnum;

$queue = new PriorityQueue(PriorityQueueTypeEnum::Max); // capacity 10 by default, forwarded to the MaxHeap

$queue->insert('low', 1);
$queue->insert('urgent', 9);
$queue->insert('medium', 5);

$queue->peek()->getValue();    // 'urgent' — peek()/extract() return the PriorityQueueNode itself
$queue->extract()->getValue(); // 'urgent', then 'medium', then 'low'
```

Passing `PriorityQueueTypeEnum::Min` instead flips extraction order to
lowest-priority-first, with an identical API:

```php
$queue = new PriorityQueue(PriorityQueueTypeEnum::Min);

$queue->insert('low', 1);
$queue->insert('urgent', 9);
$queue->insert('medium', 5);

$queue->peek()->getValue();    // 'low'
$queue->extract()->getValue(); // 'low', then 'medium', then 'urgent'
```

`insert(mixed $value, int|float $priority)` builds the `PriorityQueueNode`
for you; `insertMany(array $nodes)` takes an array of pre-built
`PriorityQueueNode`s and inserts each in turn via the same `insert()` path
(re-extracting `getValue()`/`getPriority()` from each one — it does not push
nodes into the heap directly). `isEmpty()`, `size()`, and `clear()` all
delegate straight through to the underlying heap (`getHeap()`), and `peek()`/
`extract()` inherit its `RuntimeException("Heap is empty")` on an empty
queue, regardless of `$type`.

Because the comparators return `0` for equal priorities, `PriorityQueue`
makes no FIFO/LIFO promise for nodes inserted with the same priority — which
one comes out first depends on the underlying heap's internal layout at
extraction time, not insertion order. This holds for both `Max` and `Min`
queues.

## Where this fits in the bigger picture

A binary heap is the standard backing structure for a priority queue — and
this library now ships both: the raw `MinHeap`/`MaxHeap` pair for when you
want full control over ordering, and `PriorityQueue` for the more common
value/priority use case, now itself selectable between highest-priority-first
(`PriorityQueueTypeEnum::Max`) and lowest-priority-first
(`PriorityQueueTypeEnum::Min`) at construction time. This library's own
`PathToOnePointO.md` roadmap calls out two things this unblocks: **heap
sort** (repeatedly `extract()` a `MaxHeap` into an array — not yet added to
`ArraySortAlgorythmes`) and **Dijkstra's algorithm** (`DijkstraAlgorithm`,
see [`12-dijkstra.md`](12-dijkstra.md), is now implemented and drives its
relaxation loop with `new PriorityQueue(PriorityQueueTypeEnum::Min)` directly
instead of a hand-rolled comparator over raw priority values).
