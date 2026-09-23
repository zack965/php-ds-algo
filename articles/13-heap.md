# Heap: MinHeap, MaxHeap & PriorityQueue

**Namespace:** `Zack\PhpDsAlgo\DataStructure\Heap`
**Classes:** `MinHeap`, `MaxHeap`, `AbstractBinaryHeap`, `PriorityQueue`, `PriorityQueueNode`
**Enum:** `Zack\PhpDsAlgo\enums\PriorityQueueTypeEnum`
**Contracts:** `IHeap`, `IPriorityQueue`

## What it is

A **binary heap** is a tree-shaped structure that always keeps the
**smallest** item (min-heap) or the **largest** item (max-heap) at the top.
You can read the top item instantly and remove it quickly. Every other item
is kept in "heap order", which costs much less than keeping everything fully
sorted.

The library provides:

- **`MinHeap`**: smallest value on top.
- **`MaxHeap`**: largest value on top.
- **`PriorityQueue`**: stores arbitrary values, each with a numeric
  **priority**, and serves them lowest-first or highest-first.

All three are **mutable**.

## How it works

The heap is stored in an array, level by level. For the element at index
`i`:

- its parent is at `floor((i − 1) / 2)`
- its children are at `2i + 1` and `2i + 2`

**Insert** places the new value at the end, then *bubbles it up*: it swaps
with its parent until the parent belongs above it.

**Extract** removes the root, moves the last element into the root's place,
then *sinks it down*: it swaps with the higher-priority child until both
children belong below it.

**Build from an array** runs a bottom-up *heapify* in **O(n)** time, which is
faster than inserting the elements one by one.

The backing array starts at capacity 16, or the capacity you choose, and
doubles when it needs more room.

## MinHeap & MaxHeap

```php
use Zack\PhpDsAlgo\DataStructure\Heap\MinHeap;
use Zack\PhpDsAlgo\DataStructure\Heap\MaxHeap;

$min = new MinHeap([5, 3, 8, 1, 9, 2, 7]); // heapified in O(n)
$min->peek();    // 1
$min->extract(); // 1
$min->extract(); // 2

$max = new MaxHeap([5, 3, 8, 1]);
$max->extract(); // 8
```

### Constructor

```php
new MinHeap(array $data = [], int $capacity = 0, ?callable $comparator = null);
new MaxHeap(array $data = [], int $capacity = 0, ?callable $comparator = null);
```

### API

```php
$heap->insert($value);
$heap->peek();              // top value, without removing it
$heap->extract();           // remove and return the top value
$heap->isEmpty();
$heap->size();
$heap->getCapacity();
$heap->ensureCapacity($n);  // grow ahead of a large batch of inserts
$heap->clear();
$heap->toArray();           // internal level-order array
$heap->isValid();           // verifies the heap property; useful in tests
```

`peek()` and `extract()` throw `RuntimeException("Heap is empty")` when the
heap has no elements.

`toArray()` returns the heap's internal **level order**. To read the values
in sorted order, call `extract()` repeatedly until the heap is empty.

### Custom ordering with a comparator

A comparator lets a heap order objects, records or anything else. Write
comparators with PHP's spaceship operator `<=>`, which returns exactly
`-1`, `0` or `1`. A result of `-1` means `$a` belongs closer to the top.

```php
class Task {
    public function __construct(public string $name, public int $priority) {}
}

$heap = new MinHeap([], 0, fn(Task $a, Task $b) => $a->priority <=> $b->priority);
$heap->insert(new Task('write docs', 5));
$heap->insert(new Task('fix outage', 1));

$heap->extract()->name; // 'fix outage'
```

## PriorityQueue

`PriorityQueue` pairs any value with a numeric priority. You choose the
serving order when you create it:

- `PriorityQueueTypeEnum::Max`: highest priority first
- `PriorityQueueTypeEnum::Min`: lowest priority first

```php
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueue;
use Zack\PhpDsAlgo\enums\PriorityQueueTypeEnum;

$queue = new PriorityQueue(PriorityQueueTypeEnum::Max); // optional 2nd arg: capacity (default 10)

$queue->insert('low', 1);
$queue->insert('urgent', 9);
$queue->insert('medium', 5);

$queue->peek()->getValue();    // 'urgent'
$queue->extract()->getValue(); // 'urgent', then 'medium', then 'low'
```

With `PriorityQueueTypeEnum::Min`, the same inserts come out as `'low'`,
`'medium'`, `'urgent'`.

### API

```php
$queue->insert(mixed $value, int|float $priority);
$queue->insertMany([new PriorityQueueNode('a', 3), new PriorityQueueNode('b', 7)]);
$queue->peek();     // PriorityQueueNode (getValue(), getPriority())
$queue->extract();  // PriorityQueueNode
$queue->isEmpty();
$queue->size();
$queue->clear();
```

`PriorityQueue` handles priority comparisons internally, so you never write
a comparator for it. Items with **equal** priority come out in heap order,
so give items distinct priorities when their relative order matters.

## Complexity

| Operation | Time |
|---|---|
| `insert` | O(log n) amortized |
| `peek` | O(1) |
| `extract` | O(log n) |
| Build from array (`new MinHeap($data)`) | O(n) |
| `isEmpty` / `size` / `getCapacity` | O(1) |
| `clear` | O(capacity) |
| `toArray` / `isValid` | O(n) |
| Drain in sorted order | O(n log n) |

Space: O(n).

## When to use it

- **Task scheduling by priority:** job runners, ticket triage, event
  processing.
- **Shortest-path and graph algorithms:** [Dijkstra](12-dijkstra.md) runs on
  `PriorityQueue`, and Prim's algorithm uses the same pattern.
- **Top-K problems:** keep the K largest items with a `MinHeap` of size K,
  or the K smallest with a `MaxHeap`.
- **Merging sorted streams:** combine K sorted lists by always taking the
  smallest head.
- **Running medians:** pair a `MaxHeap` for the lower half with a `MinHeap`
  for the upper half.
- **Event simulation:** always process the event with the earliest
  timestamp next.
- **Sorting:** [heap sort](08-sorting-algorithms.md) is built on `MinHeap`.

## When to choose something else

- **Strict arrival order:** `Queue`.
- **Most recent item first:** `ArrayStack`.
- **Searching for arbitrary values, or reading everything in sorted order
  many times:** `BinarySearchTree` keeps all values ordered for in-order
  traversal and range queries.
- **Lookup by key:** `HashMap`.
