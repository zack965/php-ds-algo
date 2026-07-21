# php-ds-algo

A personal PHP learning repo implementing classic data structures and algorithms from scratch, PSR-4 autoloaded under `Zack\PhpDsAlgo\`. No framework, no HTTP layer — everything is exercised via `src/index.php` or the PHPUnit test suite.

## Requirements

- PHP >= 8.1 (uses `match`, union types, readonly-style immutable classes)
- Composer

## Setup

```bash
composer install
composer dump-autoload   # regenerate the PSR-4 autoload map after adding/moving classes
```

## Usage

```bash
php src/index.php              # run the scratch demo entrypoint
php -l src/path/To/File.php    # lint a single file for syntax errors
composer test                  # run the PHPUnit suite (vendor/bin/phpunit)
```

## What's implemented

### Data structures

All data structures follow a persistent/immutable style: private constructor, static factories (`empty()`, `of()`, `fromIterable()`, `fromNodes()`), and mutating-looking operations (`append`, `insert`, `removeAt`, ...) return a **new instance** instead of mutating the receiver.

- **`SingleLinkedList`** (`src/DataStructure/LinkedList/Single`) — implements `ILinkedList`. Full method set: `prepend`/`append`/`insert`, `removeByValue`/`removeAt`/`removeHead`/`removeTail`/`clear`, `get`/`getTail`/`contains`/`indexOf`, `reverse`/`toArray`/`toArrayValues`, and functional `map`/`filter`/`reduce`.
- **`DoublyLinkedList`** (`src/DataStructure/LinkedList/Doubly`) — implements `IDoublyLinkedList`, now at parity with `SingleLinkedList`'s method set (prepend/append/insert, all removal variants, access, `reverse`, functional methods).
- **`Graph`** (`src/DataStructure/Graph`) — adjacency-list backed, supports directed/undirected and weighted/unweighted edges. Node/edge CRUD (`addNode`, `removeNode`, `addEdge`, `removeEdge`, `hasEdge`, `getEdge`), adjacency queries (`getNeighbors`, `getOutgoingEdges`, `getIncomingEdges`, `getIncidentEdges`), and iteration. See `Graph.md` for the full target interface and roadmap.

### Algorithms

- **Sorting** (`ArraySortAlgorythmes`) — bubble sort, selection sort, insertion sort.
- **Searching** (`ArraySearchAlogorthme`) — binary, exponential, interpolation, and jump search (recursive).
- **Sliding window** (`SlidingWindow`) — fixed-size sliding window traversal via callback.
- **Graph traversal** (`GraphBreadthFirstTraversal`, `GraphDepthFirstTraversal`) — BFS/DFS over an `IGraph`.
- **General array helpers** (`GeneralArrayAlgorithms`) — `hasDuplicates`, `contains`.

### Tests

PHPUnit tests under `tests/Unit/` cover `SingleLinkedList`, `DoublyLinkedList`, and `Graph`.

## What's next

Per `features.md` (full backlog) and `Graph.md` (graph-specific roadmap), smallest-effort-first:

1. **Graph algorithms** — Dijkstra's shortest path, topological sort, cycle detection (directed/undirected), connected components — natural next step now that `Graph` + BFS/DFS exist.
2. **Stack** and **Queue** — array-backed and linked-list-backed; no new interface-shape debate, good next data structures.
3. **Merge sort / quick sort** — extend `ArraySortAlgorythmes`, reuse `AlgorythmesGlobalHelpers::swapValuesOfArray`.
4. **Binary Search Tree** — first non-linear structure, forcing function for an `IBinaryTree`-style contract.

Longer backlog: circular linked list, deque, priority queue/heap, AVL/Red-Black trees, trie, hash table, disjoint set, skip list, segment/Fenwick tree, dynamic programming and backtracking algorithm sets. See `features.md` for the complete list.
