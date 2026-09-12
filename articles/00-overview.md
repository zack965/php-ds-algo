# php-ds-algo: what's in here and how it's built

`zack965/php-ds-algo` is a from-scratch PHP library of classic data structures
and algorithms, namespaced under `Zack\PhpDsAlgo\` and autoloaded via PSR-4
from `src/`. No framework, no HTTP layer — just data structures, algorithms,
and a PHPUnit test suite. This folder is a set of articles that walk through
*how* each piece actually works internally, not just how to call it (the
README already covers the call-it side).

## The two halves of the codebase

The code splits cleanly into two families that don't depend on each other:

- **`src/DataStructure/`** — containers: `SingleLinkedList`, `CircularLinkedList`,
  `DoublyLinkedList`, `ArrayStack`, `Queue`/`Deque`, `Graph`, `MinHeap`/`MaxHeap`,
  `HashTable`/`HashMap`, `Set`, `BinaryTree`/`BinarySearchTree`. Each lives in
  its own subfolder alongside a node class where relevant.
- **`src/Algorithmes/`** — static utility classes that operate on plain PHP
  arrays or on an `IGraph`: sorting, searching, string matching, sliding
  window, graph traversal/cycle-detection, edit distance. (Yes, `Algorithmes`
  and `Alogorthme` are intentional misspellings, kept consistent everywhere
  for PSR-4 resolution — see `CLAUDE.md`.)

Two architectural decisions show up repeatedly and are worth understanding
before reading the individual articles:

## 1. Linked lists and the graph are *persistent* (immutable) data structures

`SingleLinkedList` and `DoublyLinkedList` never mutate the receiver. Every
operation that "changes" the list — `append`, `insert`, `removeAt`,
`reverse`, `map`, `filter` — returns a **new** list instance and leaves the
original untouched:

```php
$a = SingleLinkedList::of([1, 2, 3]);
$b = $a->append(4);

$a->toArrayValues(); // [1, 2, 3]  — unchanged
$b->toArrayValues(); // [1, 2, 3, 4]
```

Internally this is done with a `cloneNodes()` helper that deep-copies the
existing node chain, splices the requested change into the *clone*, and
wraps the clone's new head in a new list object via the private constructor
(lists can only be built through static factories — `of()`, `fromNodes()`,
`fromIterable()`, `empty()` — never `new SingleLinkedList()` directly). See
[`01-single-linked-list.md`](01-single-linked-list.md) (which also covers
`CircularLinkedList` — same pattern, the tail's `next` just wraps back to
the head instead of `null`) and
[`02-doubly-linked-list.md`](02-doubly-linked-list.md) for the mechanics.

`ArrayStack` and `Queue`, by contrast, are ordinary mutable structures —
`push()`/`pop()`/`enqueue()`/`dequeue()` change the object in place. That
split is deliberate, not an oversight: linked lists model the "structural
sharing" persistent-data-structure pattern; stack/queue model the
conventional mutable-container pattern most PHP code expects. `Deque`
extends `Queue` and inherits its mutability directly (see
[`04-queue.md`](04-queue.md)). `Graph` is mutable too — nodes and edges are
added/removed on the same instance. So are `MinHeap`/`MaxHeap` —
`insert()`/`extract()`/`clear()` mutate the heap's backing array directly,
though unlike `ArrayStack`/`Queue` they don't return `$this` for chaining
(see [`13-heap.md`](13-heap.md)) — and so are `HashTable`/`HashMap`, same
shape as the heaps (see [`14-hashtable-hashmap.md`](14-hashtable-hashmap.md)).
`Set` is mutable the same way for `add()`/`remove()`/`clear()`, but its
`union()`/`intersection()`/`difference()` are pure and always return a
**new** `Set` (see [`15-set.md`](15-set.md)) — the one data structure in
this library mixing both styles on the same class. `BinaryTree` and
`BinarySearchTree` are mutable too, the same way as the rest of this
paragraph — `insert()`/`remove()`/`balance()` change nodes in place — not
the clone-then-splice pattern the linked lists use, despite both being
tree-shaped rather than linear (see
[`16-binary-search-tree.md`](16-binary-search-tree.md)).

## 2. Contracts (`Zack\PhpDsAlgo\Contracts\*`) pin down parity

`ILinkedList`, `IDoublyLinkedList`, `IStack`, `IQueue`, `IGraph`, `IHeap`,
`IHashTable`, `IHashMap`, and `ISet` exist so
that, for example, `SingleLinkedList` and `DoublyLinkedList` can't drift apart
in method surface — every insertion/removal/access/transformation/functional
method on one exists on the other, even though PHP interfaces can't enforce
static factory methods (`of()`, `empty()`, etc. are documented by convention
instead). Centralized `ErrorMessages` constants (`src/Constants/ErrorMessages.php`)
and dedicated exception classes (`src/Exception/`) keep error handling
consistent across implementations instead of inlining ad-hoc message strings.

## Article index

**Data structures**
- [Single Linked List & Circular Linked List](01-single-linked-list.md)
- [Doubly Linked List](02-doubly-linked-list.md)
- [Array Stack](03-array-stack.md)
- [Queue & Deque](04-queue.md)
- [Graph](05-graph.md)
- [Heap: MinHeap & MaxHeap](13-heap.md)
- [HashTable & HashMap](14-hashtable-hashmap.md)
- [Set](15-set.md)
- [BinaryTree & BinarySearchTree](16-binary-search-tree.md)

**Algorithms**
- [Graph Traversal: BFS & DFS](06-graph-traversal-bfs-dfs.md)
- [Directed Cycle Detection](07-graph-cycle-detection.md)
- [Sorting Algorithms](08-sorting-algorithms.md)
- [Searching Algorithms](09-searching-algorithms.md)
- [Sliding Window](10-sliding-window.md)
- [Levenshtein (Edit) Distance](11-levenshtein-distance.md)
- [Dijkstra's Algorithm](12-dijkstra.md)
- [KMP Substring Search](17-kmp.md)

Each article covers: what the thing does, how its internals actually work
(walked through, not just linked), time/space complexity, and the sharp
edges worth knowing about.
