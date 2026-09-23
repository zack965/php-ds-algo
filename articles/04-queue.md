# Queue & Deque

**Namespace:** `Zack\PhpDsAlgo\DataStructure\Queue`
**Classes:** `Queue`, `Deque`
**Contracts:** `Zack\PhpDsAlgo\Contracts\IQueue`, `Zack\PhpDsAlgo\Contracts\IDeque`

## What it is

A **queue** is a First-In, First-Out (FIFO) collection. Items join at the
**rear** and leave from the **front**, like a line at a ticket counter.

A **deque** (double-ended queue) can add and remove items at **both** ends.

Both classes store their items in a PHP array and are **mutable**. `Queue`
also supports an optional **maximum capacity**, which makes it a bounded
buffer.

## Queue

```php
use Zack\PhpDsAlgo\DataStructure\Queue\Queue;

$queue = new Queue();
$queue->enqueue('a')->enqueue('b')->enqueue('c');

$queue->front();   // 'a'
$queue->rear();    // 'c'
$queue->dequeue(); // 'a'
$queue->count();   // 2
```

### Creating a queue

```php
new Queue();                 // empty, unbounded
new Queue(['a', 'b']);       // with initial items (front first)
new Queue([], 100);          // with a maximum capacity of 100
```

### Bounded queues

A capacity limits how many items the queue holds:

```php
$queue = new Queue([], 2);
$queue->enqueue(1)->enqueue(2);
$queue->isFull(); // true

$queue->setMaxCapacity(10); // raise the limit at any time
$queue->getMaxCapacity();   // 10
```

The capacity always stays at least as large as the number of items already
in the queue. The constructor and `setMaxCapacity()` both validate this.

### API

```php
$queue->enqueue($item);  // add at the rear, returns $this
$queue->dequeue();       // remove and return the front
$queue->front();         // read the front
$queue->rear();          // read the rear
$queue->isEmpty();
$queue->isFull();
$queue->count();         // also count($queue)
$queue->contains($item); // strict (===) comparison
$queue->clear();         // returns $this
$queue->toArray();       // front → rear
$queue->toIterable();    // generator, front → rear
```

- `dequeue()` throws `InvalidArgumentException` when the queue is empty.
- `enqueue()` throws `InvalidArgumentException` when the queue is full.
- Call `isEmpty()` before `front()` and `rear()`.
- `toIterable()` returns a generator, so you can read a large queue lazily
  without building an array.

## Deque

`Deque` extends `Queue`, so it inherits the whole API above and adds two
operations for the opposite ends:

```php
use Zack\PhpDsAlgo\DataStructure\Queue\Deque;

$deque = new Deque([2, 3]);
$deque->enqueueFront(1);   // [1, 2, 3]
$deque->enqueue(4);        // [1, 2, 3, 4]

$deque->dequeue();         // 1 (from the front)
$deque->dequeueTail();     // 4 (from the rear)
```

| Operation | End |
|---|---|
| `enqueue` | rear |
| `enqueueFront` | front |
| `dequeue` | front |
| `dequeueTail` | rear |

## Complexity

| Operation | Time |
|---|---|
| `enqueue` | O(1) amortized |
| `dequeue` | O(n) |
| `enqueueFront` (`Deque`) | O(n) |
| `dequeueTail` (`Deque`) | O(1) |
| `front` / `rear` | O(1) |
| `isEmpty` / `isFull` / `count` | O(1) |
| `clear` / `toArray` | O(1) |
| `contains` | O(n) |
| `toIterable` | O(1) to start, O(n) to consume |

Space: O(n).

## When to use it

**Queue**
- **Task and job processing** in arrival order.
- **Breadth-first search** and level-order traversal of trees and graphs.
  `BinaryTree` uses `Queue` internally for exactly this.
- **Buffers** between a producer and a consumer. Use a capacity to put
  backpressure on the producer.
- **Rate-limited or bounded work lists.**

**Deque**
- **Sliding-window** problems where items enter at one end and leave at the
  other.
- **Work-stealing** schedulers: take from your own end and steal from the
  opposite end.
- **Palindrome checks** and other algorithms that compare both ends.
- **Undo history with a size limit:** push new actions at one end and drop
  the oldest from the other.

## When to choose something else

- **Last-in, first-out:** `ArrayStack`.
- **Processing by priority instead of arrival order:** `PriorityQueue`.
- **Fast membership checks on a large collection:** `HashTable` or `Set`.
