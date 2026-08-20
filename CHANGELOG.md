# Changelog

All notable changes to `zack965/php-ds-algo` are documented in this file,
release by release, newest first. Format loosely follows
[Keep a Changelog](https://keepachangelog.com/); versions follow
[Semantic Versioning](https://semver.org/).

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
