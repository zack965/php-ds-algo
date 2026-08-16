# php-ds-algo

A PHP library implementing classic data structures and algorithms from scratch, PSR-4 autoloaded under `Zack\PhpDsAlgo\`. No framework, no HTTP layer, no external runtime dependencies — just data structures, algorithms, and a PHPUnit test suite.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Data Structures](#data-structures)
  - [SingleLinkedList](#singlelinkedlist)
  - [DoublyLinkedList](#doublylinkedlist)
  - [ArrayStack](#arraystack)
  - [Queue](#queue)
  - [Graph](#graph)
- [Algorithms](#algorithms)
  - [Sorting — ArraySortAlgorythmes](#sorting--arraysortalgorythmes)
  - [Searching — ArraySearchAlogorthme](#searching--arraysearchalogorthme)
  - [Sliding Window — SlidingWindow](#sliding-window--slidingwindow)
  - [Edit Distance — LevenshteinDistance](#edit-distance--levenshteindistance)
  - [Graph Traversal — BFS / DFS](#graph-traversal--bfs--dfs)
  - [General Array Helpers — GeneralArrayAlgorithms](#general-array-helpers--generalarrayalgorithms)
  - [Low-level Helpers — AlgorythmesGlobalHelpers](#low-level-helpers--algorythmesglobalhelpers)
- [Exceptions & Error Handling](#exceptions--error-handling)
- [Known Quirks & Gotchas](#known-quirks--gotchas)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Roadmap](#roadmap)
- [License](#license)

## Requirements

- PHP >= 8.1
- Composer

## Installation

```bash
composer require zack965/php-ds-algo
```

For local development against a clone of this repo:

```bash
composer install
composer dump-autoload   # regenerate the PSR-4 autoload map after adding/moving classes
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList;
use Zack\PhpDsAlgo\DataStructure\Stack\ArrayStack;
use Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes;

$list = SingleLinkedList::of([3, 1, 2])->append(4)->prepend(0);
echo implode(', ', $list->toArrayValues()); // 0, 3, 1, 2, 4

$stack = ArrayStack::of(1, 2, 3);
echo $stack->pop(); // 3

$sorted = ArraySortAlgorythmes::bubbleSort([5, 3, 1, 4, 2]);
echo implode(', ', $sorted); // 1, 2, 3, 4, 5
```

---

## Data Structures

All data structures live under `src/DataStructure/`. **`SingleLinkedList`, `DoublyLinkedList`, and `Graph` are persistent/immutable**: operations that look like mutations (`append`, `insert`, `removeAt`, `addNode`, ...) never change the receiver — they return a **new instance** and leave the original untouched. **`ArrayStack` and `Queue` are the opposite: genuinely mutable.** Their "mutating" methods change the object in place and return `$this` for chaining. Keep this split in mind — it's the single biggest behavioral difference between the two groups.

### SingleLinkedList

`Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList` — implements `ILinkedList`, `IteratorAggregate`. Private constructor; build one via a static factory.

#### Creating a list

```php
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedListNode;

SingleLinkedList::empty();                 // empty list
SingleLinkedList::of([1, 2, 3]);           // from an array of values
SingleLinkedList::fromIterable($generator); // from any iterable (foreach-based)
SingleLinkedList::fromNodes([
    new SingleLinkedListNode(1),
    new SingleLinkedListNode(2),
]);                                        // from pre-built nodes (linked together for you)
SingleLinkedList::ofObjects($nodes);       // alias for fromNodes()
```

#### Insertion (each call returns a new list)

```php
$list = SingleLinkedList::of([2, 3]);

$list->prepend(1);              // [1, 2, 3]
$list->append(4);               // [2, 3, 4]
$list->insert(99, 1);           // [2, 99, 3]  — insert 99 at index 1
$list->insertBeforeNode(3, 99); // [2, 99, 3]  — insert 99 immediately before the node holding 3
$list->insertAfterNode(2, 99);  // [2, 99, 3]  — insert 99 immediately after the node holding 2

// $list itself is untouched by all of the above — capture the return value:
$list = $list->append(4);
```

`insertBeforeNode()`/`insertAfterNode()` locate the target by value (via `indexOf()`, loose `==` comparison) and throw `InvalidArgumentException` (`ErrorMessages::NO_NODE_WITH_THIS_VALUE`) if it isn't found. Inserting before the head or after the tail both work correctly (no need to special-case list boundaries yourself).

#### Removal

```php
$list->removeByValue(2);  // removes the first node holding 2
$list->removeAt(0);       // removes by index
$list->removeHead();
$list->removeTail();
$list->clear();           // empties the list — throws if already empty
$list->clearAndKeepHead(); // truncates to just the head node
```

#### Access

```php
$list->get(1);        // SingleLinkedListNode at index 1
$list->getTail();      // last node
$list->contains(2);    // returns the matching node, or throws
$list->indexOf(2);     // returns the index, or throws
$list->getLength();    // int
$list->getHead();      // ?SingleLinkedListNode
```

#### Transformations & functional methods

```php
$list->reverse();                              // new, reversed list
$list->toArray();                              // SingleLinkedListNode[]
$list->toArrayValues();                        // raw values, e.g. [1, 2, 3]
$list->map(fn($v) => $v * 2);                  // new list
$list->filter(fn($v) => $v % 2 === 0);         // new list
$list->reduce(fn($carry, $v) => $carry + $v, 0); // scalar
```

#### Iteration

`SingleLinkedList` implements `IteratorAggregate` — `foreach` yields **`SingleLinkedListNode` objects**, not raw values:

```php
foreach ($list as $node) {
    echo $node->getValue();
}
```

All error paths throw `InvalidArgumentException` with a message from `Zack\PhpDsAlgo\Constants\ErrorMessages` (`LINKEDLIST_IS_EMPTY`, `INDEX_OUT_OF_BOUND`, `NO_NODE_WITH_THIS_VALUE`).

---

### DoublyLinkedList

`Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedList` — implements `IDoublyLinkedList`, `IteratorAggregate` (note: **not** `ILinkedList` — it's a separate interface). Same persistent/immutable pattern and near-identical API surface to `SingleLinkedList`, plus backward traversal via `DoublyLinkedListNode::getPrevious()`.

```php
use Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedList;

$list = DoublyLinkedList::of([1, 2, 3]);

$list = $list->append(4)->prepend(0);          // [0, 1, 2, 3, 4]
$list = $list->insertBeforeNode(2, 99);        // insert 99 before the node holding 2
$list = $list->insertAfterNode(2, 99);         // insert 99 after the node holding 2
$list = $list->removeByValue(99);
$list = $list->removeAt(0);
$list = $list->reverse();

foreach ($list as $node) {
    echo $node->getValue(), ' prev=', $node->getPrevious()?->getValue();
}
```

Supported operations mirror `SingleLinkedList` exactly: `prepend`, `append`, `insert`, `insertBeforeNode`, `insertAfterNode`, `removeByValue`, `removeAt`, `removeHead`, `removeTail`, `clear`, `clearAndKeepHead`, `get`, `getTail`, `contains`, `indexOf`, `reverse`, `toArray`, `toArrayValues`, `map`, `filter`, `reduce`.

`DoublyLinkedListNode` differs slightly from `SingleLinkedListNode`: it has `getPrevious()`/`setPrevious(?DoublyLinkedListNode $previous)` in addition to `getNext()`/`setNext()`, but **has no `setValue()`** — nodes are value-immutable once created (only `SingleLinkedListNode` lets you mutate a node's value in place).

> **Gotcha:** `DoublyLinkedList::fromIterable()` only works with a real `array` (or another `Countable` + `ArrayAccess` iterable) — it internally does `count($values)` and `$values[$i]` index access, so passing a `Generator` throws a `TypeError`/`Error`. `SingleLinkedList::fromIterable()` doesn't have this restriction (it uses a plain `foreach`). If you need to build a `DoublyLinkedList` from a generator, collect it into an array first: `DoublyLinkedList::fromIterable(iterator_to_array($generator))`.

---

### ArrayStack

`Zack\PhpDsAlgo\DataStructure\Stack\ArrayStack` — implements `IStack`, `IteratorAggregate`. **Mutable** (unlike the linked lists above) — `push`/`pop`/`clear` change the stack in place.

```php
use Zack\PhpDsAlgo\DataStructure\Stack\ArrayStack;

ArrayStack::empty();
ArrayStack::of(1, 2, 3);              // variadic
ArrayStack::fromArray([1, 2, 3]);
ArrayStack::fromIterable($iterable);
new ArrayStack([1, 2, 3]);            // constructor is public too

$stack = ArrayStack::of(1, 2, 3);

$stack->push(4);       // mutates $stack in place, returns $this (chainable)
$stack->pop();          // removes & returns the top value — throws if empty
$stack->peek();         // top value without removing it — throws if empty
$stack->bottom();       // bottom value — throws if empty
$stack->isEmpty();      // bool
$stack->contains(2);    // bool, strict (===) comparison
$stack->clear();        // empties the stack in place, returns $this
$stack->toArray();      // bottom-to-top order
$stack->count();        // int

// Chaining, since push()/clear() return $this:
$stack->push(1)->push(2)->push(3);
```

Iteration is **top-to-bottom** and yields raw values (not nodes):

```php
foreach (ArrayStack::of(1, 2, 3) as $value) {
    echo $value; // 3, 2, 1
}
```

Errors (`pop()`/`peek()`/`bottom()` on an empty stack) throw `InvalidArgumentException` with a plain message (`"The stack is already empty"`) — not one of the `ErrorMessages` constants used by the linked lists.

---

### Queue

`Zack\PhpDsAlgo\DataStructure\Queue\Queue` — implements `IQueue` (`extends Countable`). **Mutable**, like `ArrayStack`. Has **no static factories** — construct it directly with an array, and note the capacity setup requirement below.

```php
use Zack\PhpDsAlgo\DataStructure\Queue\Queue;

$queue = new Queue([]);
$queue->setMaxCapacity(10); // REQUIRED before enqueue()/isFull()/getMaxCapacity() — see note below

$queue->enqueue('a')->enqueue('b')->enqueue('c'); // chainable, mutates in place
$queue->dequeue();      // removes & returns the front item — throws if empty
$queue->front();        // peek the front item
$queue->rear();         // peek the back item
$queue->isEmpty();      // bool
$queue->isFull();       // bool, compares count() to maxCapacity
$queue->contains('b');  // bool, strict comparison
$queue->clear();        // empties in place
$queue->toArray();      // front-to-rear order
$queue->toIterable();   // generator, front-to-rear
$queue->getMaxCapacity();
```

> **Required setup — `setMaxCapacity()`:** `maxCapacity` has no default value. Calling `enqueue()`, `isFull()`, or `getMaxCapacity()` before `setMaxCapacity()` throws a PHP `Error` ("must not be accessed before initialization"), **not** an `InvalidArgumentException`. Always call `setMaxCapacity()` right after construction if you'll use any of those three methods.

> **`Queue` does not implement `IteratorAggregate`** — you cannot `foreach` a `Queue` directly. Use `toIterable()` or `toArray()` instead.

See [Known Quirks & Gotchas](#known-quirks--gotchas) for two more Queue-specific edge cases (capacity off-by-one, `front()`/`rear()` on an empty queue) worth knowing about before relying on them.

---

### Graph

`Zack\PhpDsAlgo\DataStructure\Graph\Graph` — implements `IGraph`. **Persistent/immutable** like the linked lists. Supports directed/undirected and weighted/unweighted graphs, backed by an adjacency list of `GraphEdge` objects.

```php
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

$graph = new Graph(directed: true); // defaults as shown; `weighted` isn't a constructor
                                     // flag — it's inferred implicitly the first time
                                     // addEdge() is called with a non-null weight

$graph->addNode('A')->addNode('B')->addNode('C');
$graph->addEdge('A', 'B');
$graph->addEdge('A', 'C', weight: 5, metadata: ['label' => 'road']);

$graph->hasNode('A');            // bool
$graph->hasEdge('A', 'B');       // bool
$graph->getEdge('A', 'C')->getWeight();   // 5 (or null if unweighted)
$graph->getNeighbors('A');       // GraphEdge[] leaving 'A'
$graph->getOutgoingEdges('A');   // same idea, scanning the whole adjacency list
$graph->getIncomingEdges('C');   // edges arriving at 'C'
$graph->getIncidentEdges('A');   // edges touching 'A' in either direction

$graph->getNodes();              // ['A', 'B', 'C']
$graph->getNodesCount();         // 3
$graph->getEdgeCount();          // 2
$graph->isDirected();            // bool
$graph->isWeighted();            // bool
$graph->isEmpty();               // bool

$graph->removeEdge('A', 'B');
$graph->removeNode('C');         // also drops every edge touching 'C'
$graph->clearEdges();            // keeps nodes, drops all edges
$graph->clear();                 // drops everything

$graph->display(); // pretty-prints the adjacency list to stdout

$graph->getAdjencyMetrix();      // square adjacency matrix (see below)
$graph->printAdjacencyMatrix();  // pretty-prints that matrix to stdout
```

#### Adjacency matrix

`getAdjencyMetrix()` builds a square matrix from the graph's current nodes: row 0 and column 0 hold the node labels (in insertion order), and cell `[i][j]` is `1` when an edge exists from the i-th node to the j-th node, `0` otherwise. Returns `[]` for an empty graph.

```php
$graph = new Graph();
$graph->addNode('A')->addNode('B')->addNode('C');
$graph->addEdge('A', 'B');
$graph->addEdge('A', 'C');

$graph->getAdjencyMetrix();
// [
//     0 => [1 => 'A', 2 => 'B', 3 => 'C'],
//     1 => [0 => 'A', 1 => 0, 2 => 1, 3 => 1],
//     2 => [0 => 'B', 1 => 0, 2 => 0, 3 => 0],
//     3 => [0 => 'C', 1 => 0, 2 => 0, 3 => 0],
// ]

$graph->printAdjacencyMatrix();
//     A  B  C
//    +---------
// A  | 0  1  1
// B  | 0  0  0
// C  | 0  0  0
```

For an undirected graph the matrix is symmetric only if you've added the reciprocal edge yourself (`addEdge()` doesn't auto-mirror it — see the caveat below). `printAdjacencyMatrix()` prints `"Graph is empty."` instead of a matrix when there are no nodes.

#### Building from an adjacency list

`buildFromAdjencyList()` maps each source node to a list of edge rows. Each row is `['destination' => ..., 'weight' => ..., 'metadata' => ...]`, where `destination` is required and `weight`/`metadata` are optional (defaulting to `null` and `[]`, same defaults as `addEdge()`):

```php
$graph = new Graph();
$graph->buildFromAdjencyList([
    'A' => [
        ['destination' => 'B', 'weight' => 4.5, 'metadata' => ['label' => 'road']],
        ['destination' => 'C', 'weight' => 2.0],
    ],
    'B' => [
        ['destination' => 'C', 'weight' => 1.0],
    ],
]);
// nodes A, B, C are created automatically (including 'C', which only ever
// appears as a destination); edges A→B, A→C, B→C are added, each with
// its own weight/metadata; the graph becomes weighted as a result.
// buildFromAdjencyList() clears any existing graph state first.
```

Rows without a `weight` key produce an unweighted edge (`weight` defaults to `null`) — mixing weighted and unweighted rows across the whole call throws `InvalidArgumentException`, same as calling `addEdge()` directly. The graph's `isWeighted()` flag is set implicitly from the first edge added this way, same as with manual `addEdge()` calls.

#### Traversal

`Graph` doesn't have `bfs()`/`dfs()` methods itself — traversal is done via separate algorithm classes (see [Graph Traversal](#graph-traversal--bfs--dfs) below):

```php
use Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal;
use Zack\PhpDsAlgo\Algorithmes\GraphDepthFirstTraversal;

GraphBreadthFirstTraversal::traverse($graph, 'A'); // ['A', 'B', 'C']
GraphDepthFirstTraversal::traverse($graph, 'A');   // ['A', 'C', 'B']
```

`addEdge()` allows duplicate edges to the same destination (no uniqueness check) — calling `addEdge('A', 'B')` twice gives you two distinct `GraphEdge` objects in `getNeighbors('A')`.

`GraphEdge` (`getSourceNode()`, `getDestinationNode()`, `getWeight(): int|float|null`, `getMetadata(): array`) and `GraphNode` (`getValue()`) are separate small value-object classes under the same namespace. **`GraphNode` is currently a standalone class — `Graph` stores nodes as plain `int|string` values internally and never actually constructs a `GraphNode`.** `GraphEdge`, by contrast, is used throughout `Graph`'s adjacency list.

> **`Graph` cannot be `foreach`'d directly.** It has a `getIterator(): Traversable` method, but the class only `implements IGraph` — and `IGraph` does **not** extend `IteratorAggregate`. So `foreach ($graph as ...)` silently iterates zero times (PHP falls back to iterating public properties, and `Graph` has none) rather than erroring or calling `getIterator()`. Call `getIterator()` explicitly instead:
> ```php
> foreach ($graph->getIterator() as $sourceNode => $edges) {
>     // $edges is that node's GraphEdge[]
> }
> ```

Error handling: `addNode()` on a duplicate throws `DuplicateNodeException`; `getEdge()` on a missing edge throws `EdgeNotFoundException`; traversal helpers throw `NotFoundException` for an unknown start node; most other node/edge lookups throw plain `InvalidArgumentException`.

---

## Algorithms

Algorithm classes live under `src/Algorithmes/` and are static-method utility classes operating on plain PHP arrays — fully decoupled from the data structures above.

### Sorting — `ArraySortAlgorythmes`

`Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes`

```php
use Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes;

ArraySortAlgorythmes::bubbleSort([5, 3, 1, 4, 2]);     // [1, 2, 3, 4, 5]
ArraySortAlgorythmes::selectionSort([5, 3, 1, 4, 2]);  // [1, 2, 3, 4, 5]
ArraySortAlgorythmes::insertionSort([5, 3, 1, 4, 2]);  // [1, 2, 3, 4, 5]
ArraySortAlgorythmes::MergeSort([5, 3, 1, 4, 2]);      // [1, 2, 3, 4, 5]
ArraySortAlgorythmes::QuickSOrt([5, 3, 1, 4, 2]);      // [1, 2, 3, 4, 5]
```

All five take an array by value and return a new sorted array — the input is never mutated. This is the canonical sorting implementation; **`src/SortingAlgorithms.php`** (top-level `Zack\PhpDsAlgo` namespace) is a legacy duplicate of `selectionSort()` kept only for backward compatibility — don't build new code against it.

`MergeSort()` and `QuickSOrt()` keep their PascalCase method names (unlike the lowerCamelCase `bubbleSort`/`selectionSort`/`insertionSort`) — an inconsistency in the existing API, not a typo.

### Searching — `ArraySearchAlogorthme`

`Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme` — all seven methods return the found index/key, or `-1` if not found. `binarySearch()`, `exponentialSearchImplementation()`, `interpolationSearchRecursive()`, `jumpSearch()`, `TernarySearchAlgorythme()`, and `FibonacciSearchALgorythme()` all require a **sorted** array; `linearSearch()` does not.

```php
use Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme;

// binarySearch(array $nums, int $target, int $start, int $end = 0)
ArraySearchAlogorthme::binarySearch([10, 20, 30, 40, 50], 30, 0, 4); // 2

// exponentialSearchImplementation(array $nums, int $target)
ArraySearchAlogorthme::exponentialSearchImplementation([1, 2, 3, 4, 5, 6, 7, 8], 8); // 7

// interpolationSearchRecursive(array $data, int $target, int $low, int $high)
// best suited to roughly-uniformly-distributed integer data
ArraySearchAlogorthme::interpolationSearchRecursive([10, 20, 30, 40, 50], 30, 0, 4); // 2

// jumpSearch(array $data, int $target, int $jumpSize, int $start, int $end, int $jumpIndex)
// caller manages block boundaries manually — see below
ArraySearchAlogorthme::jumpSearch([1, 3, 5, 7, 9, 11, 13, 15], 11, 3, 0, 2, 0); // 5

// linearSearch(array $data, int|string $target)
// no sort required; works on list or associative arrays, returns the matching key
ArraySearchAlogorthme::linearSearch([10, 20, 30, 40, 50], 40); // 3
ArraySearchAlogorthme::linearSearch(['a' => 1, 'b' => 2, 'c' => 3], 2); // 'b'

// TernarySearchAlgorythme(array $data, int|string $target)
// splits the range into three parts per call instead of binary search's two
ArraySearchAlogorthme::TernarySearchAlgorythme([10, 20, 30, 40, 50], 30); // 2

// FibonacciSearchALgorythme(array $data, int $target)
// narrows the range using Fibonacci numbers instead of a midpoint
ArraySearchAlogorthme::FibonacciSearchALgorythme([10, 20, 30, 40, 50], 30); // 2
```

`jumpSearch()` has no convenience wrapper — you must pass the *first* block's `$start`/`$end` yourself (typically `0` and `$jumpSize - 1`) and `$jumpIndex = 0`; the method advances the block internally on each recursive call.

`linearSearch()` uses strict comparison (`===`), so `0`, `'0'`, and `false` are not interchangeable as targets, and it's O(n) regardless of ordering — reach for it only when the array isn't sorted or is too small to justify the other methods' setup cost.

`binarySearch()`'s parameter order is `($nums, $target, $start, $end = 0)` — note `$start` comes before `$end`, and `$end` is the one with a default. Passing arguments in the wrong order is a silent logic bug, not a type error, since both are `int`.

`TernarySearchAlgorythme()` is a thin wrapper — unlike `jumpSearch()`, it takes just `($data, $target)` and manages the `$low`/`$high` boundaries internally via a private recursive helper.

`FibonacciSearchALgorythme()` takes `($data, $target)` with `$target` typed strictly `int` (not `int|string` like `linearSearch()`/`TernarySearchAlgorythme()`). It relies on the public `getClosestFibonacci(int $n): array` helper (returns `['f1' => ..., 'f2' => ..., 'f3' => ...]`, the smallest Fibonacci number `>= $n` plus its two predecessors) to seed the probe range.

### Sliding Window — `SlidingWindow`

`Zack\PhpDsAlgo\Algorithmes\SlidingWindow`

```php
use Zack\PhpDsAlgo\Algorithmes\SlidingWindow;

SlidingWindow::processFixedSizeSlidingWindow(
    [1, 2, 3, 4, 5],
    3, // window size
    function (array $window, int $startIndex) {
        echo implode(',', $window), ' @ ', $startIndex, PHP_EOL;
    }
);
// 1,2,3 @ 0
// 2,3,4 @ 1
// 3,4,5 @ 2
```

If `$size <= 0` or `$size` is larger than the array, the callback is never invoked (no error).

### Edit Distance — `LevenshteinDistance`

`Zack\PhpDsAlgo\Algorithmes\LevenshteinDistance` — classic Wagner-Fischer dynamic-programming edit distance between two strings, comparing characters with `===` (case-sensitive). `calculate()` returns an array with the DP table and the reconstructed optimal path of edits, not just the distance.

```php
use Zack\PhpDsAlgo\Algorithmes\LevenshteinDistance;

$result = LevenshteinDistance::calculate('kitten', 'sitting');

$result['minimumEditDistance']; // 3
$result['path'];                // list of ['step' => 'Match'|'Substitute'|'Insert'|'Delete', 'from' => ..., 'to' => ..., 'direction' => ...],
                                 // walked from the last character back to the first
$result['matrix'];              // the full DP table, headers included
$result['WordsData'];           // ['word1', 'word2', 'Word1Spaced', 'Word2Spaced', 'Xrows', 'YColumns']
```

### Graph Traversal — BFS / DFS

`Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal` / `GraphDepthFirstTraversal` — each has one static `traverse(IGraph $graph, int|string $start): array` method, returning visited nodes in traversal order. Both throw `NotFoundException` if `$start` isn't a node in the graph.

```php
use Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal;
use Zack\PhpDsAlgo\Algorithmes\GraphDepthFirstTraversal;

GraphBreadthFirstTraversal::traverse($graph, 'A');
GraphDepthFirstTraversal::traverse($graph, 'A');
```

DFS is stack-based (iterative, not recursive) — for a node with neighbors `[B, C]`, `C` is explored before `B` (last-pushed, first-popped).

### General Array Helpers — `GeneralArrayAlgorithms`

`Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms`

```php
use Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms;

GeneralArrayAlgorithms::hasDuplicates([1, 2, 3, 2]); // true
GeneralArrayAlgorithms::contains([1, 2, 3], 2);      // true, strict (===) comparison
```

### Low-level Helpers — `AlgorythmesGlobalHelpers`

`Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers` — shared primitives used internally by the sorting/searching algorithms above; also usable directly.

```php
use Zack\PhpDsAlgo\Helpers\Algorythmes\AlgorythmesGlobalHelpers;

AlgorythmesGlobalHelpers::isBetween(5, 1, 10); // true, inclusive on both ends

$nums = [1, 2, 3];
AlgorythmesGlobalHelpers::swapValuesOfArray($nums, 0, 2); // by reference; $nums is now [3, 2, 1]
// throws InvalidArgumentException if either index doesn't exist in the array


## Exceptions & Error Handling

| Exception | Thrown by | Notes |
|---|---|---|
| `InvalidArgumentException` (SPL) | Most linked-list, stack, and queue error paths | Linked lists use constants from `Zack\PhpDsAlgo\Constants\ErrorMessages` (`LINKEDLIST_IS_EMPTY`, `INDEX_OUT_OF_BOUND`, `NO_NODE_WITH_THIS_VALUE`); `ArrayStack`/`Queue`/`Graph` mostly use plain inline messages instead |
| `Zack\PhpDsAlgo\Exception\NotFoundException` | `GraphBreadthFirstTraversal::traverse()`, `GraphDepthFirstTraversal::traverse()` | Built via `NotFoundException::nodeNotFound($value)` |
| `Zack\PhpDsAlgo\Exception\DuplicateNodeException` | `Graph::addNode()` on a duplicate | Built via `DuplicateNodeException::nodeDuplicate($value)` |
| `Zack\PhpDsAlgo\Exception\EdgeNotFoundException` | `Graph::getEdge()` on a missing edge | Built via `EdgeNotFoundException::edgeNotFound($source, $destination)` |

```php
use Zack\PhpDsAlgo\Exception\NotFoundException;

try {
    GraphBreadthFirstTraversal::traverse($graph, 'unknown-node');
} catch (NotFoundException $e) {
    echo $e->getMessage(); // "Node with value 'unknown-node' was not found."
}
```

---

## Known Quirks & Gotchas

A few behaviors worth knowing before you rely on them — none of these are "wrong" enough to change without a deliberate decision, but all of them have surprised someone while building this library:

- **`Queue::enqueue()`'s capacity check is off by one.** It compares with `>` instead of `>=`, so a queue with `setMaxCapacity(2)` actually accepts **3** items before `enqueue()` throws, and `isFull()` briefly reports `false` again once that 3rd item is in.
- **`Queue::front()`/`Queue::rear()` don't check for an empty queue**, despite `IQueue`'s docblock promising `@throws InvalidArgumentException`. Calling either on an empty queue just triggers a PHP "undefined array key" warning and returns `null`.
- **`Queue`'s constructor doesn't reindex array keys** (no `array_values()`), unlike `ArrayStack::fromArray()`. Building a `Queue` from a non-sequential array (e.g. `[5 => 'a', 9 => 'b']`) will break `front()`/`rear()`, which assume index `0` and `count - 1`.
- **`GraphEdge::getWeight()` returns `null` for an unweighted edge** — `int|float|null`, not `int|float`. Always null-check (or use `Graph::isWeighted()`) before doing arithmetic on it.
- **A handful of defensive null/false guards are unreachable in practice.** `Graph::removeNode()`, and `insert()`/`removeAt()`/`get()` on both linked lists, each have a redundant guard clause that's already preceded by an equivalent bounds check earlier in the same method — under the classes' normal invariants they can never actually trigger. Harmless, just dead code.
- **`ArraySearchAlogorthme::interpolationSearchRecursive()` computes its estimated position with float division** and uses the (possibly fractional) result both as an array index and as a recursive `int` argument — PHP emits an implicit float-to-int-conversion deprecation notice in that case. It still returns the correct result; it's just noisy.
- **`Graph::getAdjencyMetrix()` and `Graph::printAdjacencyMatrix()` spell "adjacency"/"matrix" inconsistently** — the getter matches the existing `getAdjency()` typo (`Adjency`, `Metrix`), while the printer spells both correctly. Same method pair, two different spellings; match whichever one you're calling.

---

## Testing

```bash
composer test                                  # full suite (vendor/bin/phpunit)
vendor/bin/phpunit tests/Unit/DataStructure     # just the data-structure tests
vendor/bin/phpunit --filter QueueTest           # a single test class
vendor/bin/phpunit --coverage-text              # coverage summary (needs Xdebug or PCOV)
```

There is no other linter or static analysis tool configured — `php -l path/to/File.php` is the extent of syntax checking beyond the test suite.

---

## Project Structure

```
src/
├── Algorithmes/            # static utility classes operating on plain arrays
├── Constants/               # ErrorMessages
├── Contracts/                # interfaces: ILinkedList, IDoublyLinkedList, IStack, IQueue, IGraph
├── DataStructure/
│   ├── LinkedList/Single/    # SingleLinkedList, SingleLinkedListNode
│   ├── LinkedList/Doubly/    # DoublyLinkedList, DoublyLinkedListNode
│   ├── Stack/                 # ArrayStack
│   ├── Queue/                 # Queue
│   └── Graph/                  # Graph, GraphNode, GraphEdge
├── Exception/                # NotFoundException, DuplicateNodeException, EdgeNotFoundException
├── Helpers/Algorythmes/       # AlgorythmesGlobalHelpers
├── SortingAlgorithms.php      # legacy duplicate — not canonical, see Sorting section above
└── index.php                  # scratch/demo entrypoint
```

Note the intentional misspellings (`Algorythmes`, `Alogorthme`) used consistently across namespaces and folder names — they're not typos to "fix," PSR-4 resolution depends on them matching exactly.

## Roadmap

See `TODO.md` and `features.md` for the current backlog. Highlights: graph algorithms (Dijkstra, topological sort, cycle detection, connected components), a Binary Search Tree, and further out — deque, priority queue/heap, AVL/Red-Black trees, trie, hash table, disjoint set.

## License

MIT
