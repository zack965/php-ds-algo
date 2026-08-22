# Changelog

All notable changes to `zack965/php-ds-algo` are documented in this file,
release by release, newest first. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/); versions follow
[Semantic Versioning](https://semver.org/).

## [v0.4.0] — 2026-08-22

### Added

**Data structures**
- `HashTable` (`src/DataStructure/HashTabe/`, new `IHashTable` contract) — a separate-chaining
  hash table (really a hash *set*: values act as their own keys, and duplicates are allowed
  as separate entries rather than deduplicated). Generic over any hashable value
  (`@template T`) — strings hash directly, other scalars/`null`/arrays/objects via
  `serialize()`, `-0.0` canonicalized to `0.0` so it matches PHP's `-0.0 === 0.0`,
  closures/resources rejected with `InvalidArgumentException`. Auto-resizes (doubles
  capacity) once the load factor exceeds `0.7`. Implements `IteratorAggregate`/`Countable`.
  100% method/line test coverage.
- `HashMap` / `HashMapNode` (`src/DataStructure/HashTabe/`, new `IHashMap` contract) — a
  proper key-value map living alongside `HashTable`, sharing its hashing approach but keyed
  independently of the stored value. `put()` upserts (insert-or-replace); `update()` is the
  must-already-exist counterpart; `get()` returns `null` for a missing key rather than
  throwing. Same generic-key hashability rules as `HashTable`'s values — values themselves
  have no such restriction. Implements `IteratorAggregate`/`Countable`. 100% method/line
  test coverage.
- `Set` (`src/DataStructure/Set/`, new `ISet` contract) — a plain, array-backed collection
  of unique values, de-duplicated via strict `===` comparison (with two `NAN` floats treated
  as equal to each other — see `GeneralArrayAlgorithms::equals()` below). Set-algebra
  operations (`union`, `intersection`, `difference`, `isSubsetOf`, `isSupersetOf`, `equals`)
  and indexed access (`get(int $index)`, `indexOf(mixed $value)`, `update(mixed $oldValue,
  mixed $newValue)`). `union()`/`intersection()`/`difference()` are pure and always return a
  new `Set`; every other mutating method (`add`/`remove`/`clear`/`update`) changes the
  receiver in place. No hashing — membership is a linear `O(n)` scan, trading `HashTable`'s
  lookup speed for simplicity. Implements `IteratorAggregate`/`Countable`. 100% method/line
  test coverage; full internals walkthrough in `articles/15-set.md`.
- `HashSet` (`src/DataStructure/HashTabe/`, new `IHashSet` contract) — `Set`'s hashed
  sibling: each bucket is a real `Set` instance rather than a raw array, so `HashSet` is a
  genuine set (inserting an equal value twice is a no-op), unlike the bag-like `HashTable`.
  Narrower generic bound than `HashTable`/`HashMap` — `@template T of scalar|object`, so
  `null` and arrays are rejected in addition to closures/resources. Objects are hashed by
  identity (`spl_object_id()`) rather than by value (`serialize()`); membership is always
  `===`, so this only changes which bucket a lookup starts scanning, never correctness.
  `update()` never throws — an unhashable value or a no-op rename just returns `false`/`true`.
  Implements `IteratorAggregate`/`Countable`.

**Algorithms**
- `GeneralArrayAlgorithms::equals()` — a new public equality helper: strict `===`, except
  that two `NAN` floats are treated as equal to each other (`NAN === NAN` is otherwise
  always `false` in PHP). Shared internally by `contains()`/`remove()`, and called directly
  by `Set::indexOf()` (and, through it, `HashSet::getValuePosition()`/`update()`) — keeping
  every membership-adjacent check on one equality definition so "is this value present" and
  "can this value be removed" can never disagree with each other.

**Docs**
- New README sections for [`HashTable`](README.md#hashtable), [`HashMap`](README.md#hashmap),
  [`Set`](README.md#set), and [`HashSet`](README.md#hashset), plus updated Exceptions, Known
  Quirks & Gotchas, and Project Structure sections.
- `features.md` gained the `IHashTable`/`IHashMap`/`ISet`/`IHashSet` interface shapes and two
  new conventions: bounded generics (`@template T of scalar|object`) for structures whose own
  invariants rule out part of `mixed` up front, and not exposing a structure's internal
  storage through its public contract.

### Changed

- `GeneralArrayAlgorithms::contains()` — parameter type widened from `int|string` to `mixed`,
  making it genuinely generic as its `@template T` docblock already claimed.
- `GeneralArrayAlgorithms::remove()` — now matches elements using the same equality as
  `contains()` (via `equals()`) instead of a separate, plain `===` check.
- `src/index.php` demo entrypoint updated to exercise `HashTable`.

## [v0.3.0] — 2026-08-20

### Added

**Data structures**
- `MinHeap` / `MaxHeap` (`src/DataStructure/Heap/`) — array-backed binary
  heap over a shared `AbstractBinaryHeap`, implementing a new `IHeap`
  contract. Custom-comparator support (`?callable $comparator` taking
  `(mixed $a, mixed $b): int`), automatic capacity growth, `insert()` /
  `peek()` / `extract()` / `isValid()` / `toArray()`.
- `PriorityQueue` / `PriorityQueueNode` (`src/DataStructure/Heap/`) — a new
  `IPriorityQueue` contract; a value/priority wrapper around an internal
  `MaxHeap` or `MinHeap`, selected via the new `PriorityQueueTypeEnum`
  (`Max`/`Min`) constructor argument.

**Algorithms**
- `DijkstraAlgorithm` / `DijkstraAlgorithmDistance`
  (`src/Algorithmes/DijkstraAlgorithm/`) — single-source shortest paths on a
  weighted `Graph`, built on `PriorityQueue(PriorityQueueTypeEnum::Min)`.
  Stateful (construct, call `calculateDistances()`, then
  `findShortestPath()` / `display()`), unlike the rest of `Algorithmes/`.
  This replaces the earlier scaffolding-only `DijkstraAlgorithm` stub (see
  Removed below) with a full implementation and test suite
  (`tests/Unit/Algorithmes/DijkstraAlgorithm/DijkstraAlgorithmTest.php`).

**Docs / project**
- `PathToOnePointO.md` — synthesis doc scoping what "1.0" means for this
  library and sequencing the milestones (M1 tooling, M2 feature
  completeness, M3 API stability, M4 release) to get there.

### Changed

- `Graph::buildFromAdjencyList()` now handles `weight` and `metadata` per
  edge row when building a graph from an adjacency-list array, instead of
  only `destination`.
- `src/index.php` demo entrypoint updated to exercise `MinHeap`/`MaxHeap`,
  `PriorityQueue`, and `DijkstraAlgorithm`.

### Removed

- The original `src/Algorithmes/DijkstraAlgorithm.php` stub (two static
  methods, `computeToEveryNode()` / `computeBetweenTwoNodes()`, that
  validated `isWeighted()` and always returned `[]`) and its companion
  `src/Helpers/Algorythmes/DijkstraAlgorithmTable.php` — superseded by the
  stateful `DijkstraAlgorithm`/`DijkstraAlgorithmDistance` pair above.

## [v0.2.0] — 2026-08-05

### Added

**Algorithms**
- Searching (`ArraySearchAlogorthme`): linear search, ternary search,
  Fibonacci search — rounding out the search algorithm family alongside
  the existing binary/exponential/interpolation/jump search.
- `GraphDirectedCycleDetector` — cycle detection for directed graphs.

**Data structures**
- `Graph`: adjacency matrix support (`getAdjencyMetrix()` /
  `printAdjacencyMatrix()`), plus `IGraph` contract updates to match.

**Docs / project**
- `TODO.md` reconciled against what's actually shipped in `src/`.

### Changed

- `src/index.php` demo entrypoint updated to exercise the newly added
  search algorithms.

## [v0.1.1] — 2026-08-04

### Added

**Algorithms**
- Sorting (`ArraySortAlgorythmes`): merge sort, quick sort.
- `LevenshteinDistance` — edit distance via Wagner-Fischer DP, with
  reconstructed optimal path, plus README coverage.

**Docs / project**
- `CONTRIBUTING.md` — fork/branch workflow and PR expectations.

### Fixed

- Package name/command install instructions corrected.
- Whitespace cleanup.

## [v0.1.0] — 2026-07-21

Initial release.

### Added

**Data structures**
- `SingleLinkedList` (`ILinkedList` contract), immutable/persistent style
  with static factories.
- `DoublyLinkedList` (`IDoublyLinkedList` contract), including
  `insertBefore` / `insertAfter`; made immutable in the same cycle it was
  introduced.
- `ArrayStack` (`IStack` contract).
- `Queue` (`IQueue` contract).
- `Graph` + `GraphNode` + `GraphEdge` (`IGraph` contract) — directed/undirected,
  weighted/unweighted, adjacency-list based.
- Exceptions: `DuplicateNodeException`, `EdgeNotFoundException`,
  `NotFoundException`.
- Centralized `ErrorMessages` constants (moved from the earlier `Contants`
  namespace typo to `Constants`).

**Algorithms**
- Sorting (`ArraySortAlgorythmes`): bubble, selection, insertion.
- Searching (`ArraySearchAlogorthme`): initial implementation (binary,
  exponential, interpolation, jump).
- `SlidingWindow`: fixed-size window traversal.
- `GeneralArrayAlgorithms`: `hasDuplicates`, `contains`.
- Graph traversal: BFS (`GraphBreadthFirstTraversal`), DFS
  (`GraphDepthFirstTraversal`).
- Legacy top-level `SortingAlgorithms` (duplicate selection sort, different
  namespace — carried along from the start).

**Docs / project**
- README, `Graph.md`, `features.md`, `TODO.md`.
- `composer.json` PSR-4 autoloading, PHPUnit test suite (`phpunit.xml`).
