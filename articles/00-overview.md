# php-ds-algo documentation

`zack965/php-ds-algo` is a pure-PHP library of classic data structures and
algorithms, namespaced under `Zack\PhpDsAlgo\` and autoloaded via PSR-4 from
`src/`. It has no framework dependency, so you can drop it into any PHP
project.

Each article in this folder documents one data structure or algorithm family
and covers:

- **What it is**: the concept and how this library implements it
- **API**: the main methods, with examples
- **Complexity**: time and space costs of each operation
- **When to use it**: the problems it solves well
- **When to choose something else**: the cases where another structure in
  this library is the better fit

## Library layout

| Area | Namespace | Contents |
|---|---|---|
| Data structures | `Zack\PhpDsAlgo\DataStructure\*` | Linked lists, stack, queue/deque, graph, heaps & priority queue, hash table/map, set, binary trees |
| Algorithms | `Zack\PhpDsAlgo\Algorithmes\*` | Sorting, searching, sliding window, graph traversal, cycle detection, Dijkstra, Levenshtein distance, KMP |
| Contracts | `Zack\PhpDsAlgo\Contracts\*` | Interfaces (`ILinkedList`, `IStack`, `IQueue`, `IGraph`, `IHeap`, `IHashMap`, `ISet`, …) |
| Errors | `Zack\PhpDsAlgo\Constants\ErrorMessages`, `Zack\PhpDsAlgo\Exception\*` | Shared error messages and dedicated exception classes |

The `Algorithmes` / `Alogorthme` / `Algorythmes` spellings are part of the
namespace names. Use them exactly as written when you import classes.

## Two programming styles

The library supports two styles, and each structure uses the one that suits
it best.

### Persistent (immutable) structures

`SingleLinkedList`, `CircularLinkedList` and `DoublyLinkedList` are
**persistent**. Operations such as `append`, `insert`, `removeAt`, `map` and
`filter` return a **new** list and leave the original as it was:

```php
$a = SingleLinkedList::of([1, 2, 3]);
$b = $a->append(4);

$a->toArrayValues(); // [1, 2, 3]
$b->toArrayValues(); // [1, 2, 3, 4]
```

This makes the lists safe to share between parts of an application, keep as
snapshots (for example for undo history), and reason about in functional-style
code.

### Mutable containers

`ArrayStack`, `Queue`, `Deque`, `Graph`, `MinHeap`, `MaxHeap`,
`PriorityQueue`, `HashTable`, `HashMap`, `Set`, `BinaryTree` and
`BinarySearchTree` are **mutable**. Their operations update the object in
place, which is the familiar model for most PHP code. `Set` also has pure
set-algebra methods (`union`, `intersection`, `difference`) that return a new
`Set`.

## Contracts

Each structure implements an interface from `Zack\PhpDsAlgo\Contracts`. You
can type-hint against the interface (`IStack`, `IQueue`, `IGraph`, …) and swap
implementations without changing calling code. Graph algorithms such as BFS,
DFS, cycle detection and Dijkstra accept any `IGraph`.

## Article index

**Data structures**
- [Single Linked List & Circular Linked List](01-single-linked-list.md)
- [Doubly Linked List](02-doubly-linked-list.md)
- [Array Stack](03-array-stack.md)
- [Queue & Deque](04-queue.md)
- [Graph](05-graph.md)
- [Heap: MinHeap, MaxHeap & PriorityQueue](13-heap.md)
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

## Choosing a structure at a glance

| You need… | Reach for |
|---|---|
| An ordered sequence you can share safely and transform functionally | `SingleLinkedList` / `DoublyLinkedList` |
| A rotating, round-robin sequence | `CircularLinkedList` |
| Last-in, first-out processing (undo, backtracking, parsing) | `ArrayStack` |
| First-in, first-out processing (jobs, buffers, BFS) | `Queue` |
| Insertion and removal at both ends | `Deque` |
| Always taking the smallest / largest / highest-priority item | `MinHeap`, `MaxHeap`, `PriorityQueue` |
| Fast lookup by key | `HashMap` |
| Fast "have I seen this value?" checks | `HashTable` |
| Unique values with union / intersection / difference | `Set` |
| Sorted data with range, floor/ceiling, and k-th queries | `BinarySearchTree` |
| Hierarchical or shape-based data (levels, completeness) | `BinaryTree` |
| Relationships between entities (networks, dependencies, maps) | `Graph` |
