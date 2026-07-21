# Features & Roadmap Specification

This document specifies the conventions every new data structure must follow, the
common interface each one should implement, and a backlog of data structures and
algorithms not yet present in this repo.

It is a planning document, not enforced by CI — use it to keep new work consistent
with what's already here (`SingleLinkedList`, `DoublyLinkedList`, `ArraySortAlgorythmes`,
`ArraySearchAlogorthme`, `SlidingWindow`).

## 1. Conventions for every new data structure

- **Namespace**: `Zack\PhpDsAlgo\DataStructure\<Category>\<Name>`, folder mirrors namespace
  under `src/DataStructure/`. Keep the intentional `Algorythmes`/`Alogorthme` spelling
  only in the algorithms area — data structures use normal spelling.
- **Immutable / persistent style**: private constructor, only reachable through static
  factories (`empty()`, `of(array $values)`, `fromIterable(iterable $values)`,
  `fromNodes(array $nodes)` where nodes make sense). Every mutating-looking method
  (`append`, `insert`, `removeAt`, `push`, `pop`, ...) clones the internal structure and
  returns a **new instance** — never mutate `$this` in place.
- **Errors**: reuse `Zack\PhpDsAlgo\Constants\ErrorMessages`, throw
  `InvalidArgumentException`. Add new constants there instead of inlining strings
  (`DoublyLinkedList::insert`/`removeByValue` still inline messages in a few spots —
  don't copy that, align with `ErrorMessages` in new code).
- **Iteration**: implement `IteratorAggregate::getIterator()` via a generator, yielding
  nodes (matching current `SingleLinkedList`/`DoublyLinkedList` behavior).
- **Contract**: implement the relevant interface from `src/Contracts/` (see §2). Add a
  new interface per structure shape rather than forcing unrelated structures (e.g. a
  `Stack`) into the linked-list contract.
- **Tests**: add a PHPUnit test class under `tests/Unit/DataStructure/<Category>/<Name>Test.php`
  mirroring `tests/Unit/DataStructure/LinkedList/Single/SingleLinkedListTest.php`. Run with
  `composer test` / `vendor/bin/phpunit`.

## 2. Interface every list-like structure should implement

`ILinkedList` / `IDoublyLinkedList` already define this shape for singly/doubly linked
lists. Use the same method set (names and signatures) for any new **sequential**
structure (circular linked list, skip list, etc.) so they stay interchangeable:

```php
interface ISequentialStructure
{
    // Meta
    public function getLength(): int;
    public function getHead(): ?NodeType;

    // Iterator
    public function getIterator(): \Traversable;

    // Insertions
    public function prepend(mixed $value): self;
    public function append(mixed $value): self;
    public function insert(mixed $value, int $index): self;

    // Removal
    public function removeByValue(mixed $value): self;
    public function removeAt(int $index): self;
    public function removeHead(): self;
    public function removeTail(): self;
    public function clear(): self;
    public function clearAndKeepHead(): self;

    // Access
    public function get(int $index): NodeType;
    public function getTail(): NodeType;
    public function contains(mixed $value): NodeType;
    public function indexOf(mixed $value): int;

    // Transformations
    public function reverse(): self;
    public function toArray(): array;
    public function toArrayValues(): array;

    // Functional
    public function map(callable $fn): self;
    public function filter(callable $fn): self;
    public function reduce(callable $fn, mixed $initial = null): mixed;

    // Static factories (documented, not enforceable in a PHP interface)
    // public static function empty(): self;
    // public static function of(array $values): self;
    // public static function fromIterable(iterable $values): self;
    // public static function fromNodes(array $nodes): self;
}
```

For structures with a fundamentally different shape, define a narrower interface
instead of forcing this one on them:

```php
interface IStack
{
    public function push(mixed $value): self;
    public function pop(): self;
    public function peek(): mixed;
    public function isEmpty(): bool;
    public function getLength(): int;
    public function toArray(): array;
}

interface IQueue
{
    public function enqueue(mixed $value): self;
    public function dequeue(): self;
    public function peek(): mixed;
    public function isEmpty(): bool;
    public function getLength(): int;
    public function toArray(): array;
}

interface IBinaryTree
{
    public function insert(mixed $value): self;
    public function remove(mixed $value): self;
    public function contains(mixed $value): bool;
    public function getRoot(): ?NodeType;
    public function height(): int;

    // Traversals
    public function inOrder(): array;
    public function preOrder(): array;
    public function postOrder(): array;
    public function levelOrder(): array;
}

interface IGraph
{
    public function addNode(mixed $value): self;
    public function addEdge(mixed $from, mixed $to, ?float $weight = null): self;
    public function neighborsOf(mixed $value): array;
    public function bfs(mixed $start): array;
    public function dfs(mixed $start): array;
}
```

## 3. Missing data structures (backlog)

Immediate / already flagged in code:
- **Doubly linked list**: `insertBefore(NodeType $node, mixed $value)` and
  `insertAfter(NodeType $node, mixed $value)` — flagged in `TODO.md`.
- **Circular linked list** (singly and doubly variants).

Not yet started:
- **Stack** (array-backed and linked-list-backed)
- **Queue** (array-backed, linked-list-backed, and circular-buffer-backed)
- **Deque** (double-ended queue)
- **Priority Queue / Heap** (min-heap and max-heap)
- **Binary Search Tree**
- **Balanced trees**: AVL tree, Red-Black tree
- **Trie** (prefix tree)
- **Hash Table / Hash Map** (with a chosen collision strategy — chaining vs open addressing)
- **Graph** (adjacency list and adjacency matrix, directed/undirected, weighted/unweighted)
- **Disjoint Set / Union-Find**
- **Skip List**
- **Segment Tree** / **Fenwick Tree (Binary Indexed Tree)** — range query structures

## 4. Missing algorithms (backlog)

### Sorting (`ArraySortAlgorythmes` currently has bubble/selection/insertion)
- Merge sort
- Quick sort
- Heap sort
- Shell sort
- Counting sort
- Radix sort
- Bucket sort

### Searching (`ArraySearchAlogorthme` currently has binary/exponential/interpolation/jump)
- Linear search (baseline, useful for unsorted input / comparison benchmarks)
- Ternary search
- Fibonacci search

### Sliding window (`SlidingWindow` currently has fixed-size only)
- Variable-size / dynamic sliding window (grow/shrink based on a predicate)
- Sliding window maximum/minimum (monotonic deque based)

### Recursion / divide & conquer
- Tower of Hanoi
- Merge sort / quick sort (see above — divide & conquer by nature)
- Fast exponentiation (power by squaring)

### Two pointers
- Two-sum on sorted array
- Remove duplicates from sorted array in place
- Container-with-most-water style problems

### Graph algorithms (depends on a `Graph` structure existing first)
- BFS / DFS traversal
- Dijkstra's shortest path
- Bellman-Ford
- A* search
- Topological sort
- Kruskal's / Prim's minimum spanning tree
- Cycle detection (directed and undirected)

### Dynamic programming
- Fibonacci (memoized vs tabulated, to contrast with naive recursion)
- Longest common subsequence
- Longest increasing subsequence
- 0/1 Knapsack
- Edit distance (Levenshtein)
- Coin change

### String algorithms
- Naive substring search
- KMP (Knuth-Morris-Pratt)
- Rabin-Karp
- Palindrome checks / longest palindromic substring

### Backtracking
- N-Queens
- Subsets / permutations / combinations generation
- Sudoku solver

## 5. Suggested next steps (smallest-effort-first)

1. `DoublyLinkedList::insertBefore` / `insertAfter` — closes the open `TODO.md` item,
   reuses the existing clone-then-splice pattern already in the class.
2. `Stack` and `Queue` — small, no new interface shape debate, good next structures.
3. Merge sort / quick sort — natural extensions of `ArraySortAlgorythmes`, reuse
   `AlgorythmesGlobalHelpers::swapValuesOfArray`.
4. Binary Search Tree — first non-linear structure, a good forcing function for the
   `IBinaryTree`-style interface above.
