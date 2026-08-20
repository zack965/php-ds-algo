# Path to 1.0

Where `zack965/php-ds-algo` stands and what's actually left before a `v1.0.0`
tag is justified. This is the *synthesis* document — it doesn't re-list every
backlog item already tracked in `TODO.md`/`features.md`; it defines what "done"
means for 1.0 and sequences the smallest set of work that gets there.

_Written 2026-08-08 against commit `74aecb3` (tags up to `v0.2.0`); updated
2026-08-19 to reflect `MinHeap`/`MaxHeap` landing; updated 2026-08-20 to
reflect `PriorityQueue`/`PriorityQueueNode`, `DijkstraAlgorithm`, and
`CHANGELOG.md` landing (tags up to `v0.3.0`)._

## Where it stands today

- 601 tests / 1088 assertions, all green, but **7 PHPUnit deprecations**
  (unchanged from before — still M1 work, see below).
- Data structures: `SingleLinkedList`, `DoublyLinkedList` (full parity, incl.
  insert-before/after), `ArrayStack`, `Queue`, `Graph` (directed/undirected,
  weighted/unweighted, adjacency list + matrix), `MinHeap`/`MaxHeap` (array-backed
  binary heap over `AbstractBinaryHeap` + `IHeap`, custom-comparator support),
  `PriorityQueue`/`PriorityQueueNode` (`IPriorityQueue` contract, backed by
  either a `MaxHeap` or `MinHeap` per a required `PriorityQueueTypeEnum`
  constructor argument — value/priority wrapper).
- Algorithms: sorting (bubble/selection/insertion/merge/quick), searching
  (binary/exponential/interpolation/jump/linear/ternary/fibonacci), fixed-size
  sliding window, BFS/DFS, directed-graph cycle detection, Levenshtein
  distance, Dijkstra's shortest path (`DijkstraAlgorithm`/
  `DijkstraAlgorithmDistance`, `src/Algorithmes/DijkstraAlgorithm/`, stateful
  — the one exception to the static-utility shape the rest of `Algorithmes/`
  uses — built on `PriorityQueue(PriorityQueueTypeEnum::Min)`, see M2 below),
  `GeneralArrayAlgorithms` (hasDuplicates/contains).
- Docs are already strong: 43KB README, `CONTRIBUTING.md`, `Graph.md`,
  `features.md` (conventions + backlog), `TODO.md` (ranked backlog), and a
  14-article `articles/` walkthrough series (`00-overview.md` through
  `13-heap.md`, including [`12-dijkstra.md`](articles/12-dijkstra.md)).
- **No CI** — nothing runs `composer test` on push/PR. No static analysis
  (phpstan/psalm), no linter.
- Legacy duplicate `src/SortingAlgorithms.php` still shipping alongside the
  canonical `Algorithmes\ArraySortAlgorythmes`.
- `CHANGELOG.md` now exists (Keep a Changelog format, backfilled through
  `v0.1.0`, kept in sync with the per-version files under `changelogs/` —
  see M3), but there's still no explicit semver/BC policy stated in the
  README.

This lines up with `rating.md`'s self-assessment (8/10): the gap to 1.0 is
**tooling and API-stability discipline**, not raw feature count. `TODO.md`'s
backlog (tries, hash tables, AVL/Red-Black trees, skip lists, segment trees,
most of DP/string/backtracking) is effectively open-ended — treating "1.0" as
"implement everything in the backlog" means it never ships. Scope it instead.

## What "1.0" should mean here

For a library (not an app), 1.0 is a promise: *the public API is stable and
won't break without a major version bump, and what's shipped is trustworthy*.
Concretely:

1. **CI enforces the test suite on every push/PR** — no more "works on my
   machine." This is the single highest-leverage gap per `rating.md`.
2. **Zero warnings/deprecations** from `composer test`.
3. **No known-dead or duplicate code** in the public surface (`SortingAlgorithms.php`).
4. **Coverage gaps closed** on everything already shipped (100% method
   coverage, not just line coverage).
5. **The obvious category gaps closed** — as of 2026-08-08 every shipped
   structure was linear (lists/stack/queue) plus one graph, and every
   algorithm family except Levenshtein was array sorting/searching: no tree,
   no heap, no hash table, no shortest-path algorithm, no DP beyond one
   edit-distance example, no string-matching algorithm. Heap and shortest-path
   (Dijkstra) are now closed (see M2 below); tree, hash table, deque, heap
   sort, topological sort, DP, and string-matching remain gaps. Those are the
   structures/algorithms anyone evaluating a "data structures and algorithms"
   library checks for first — see the curated list in M2 below.
6. **A documented BC/versioning policy** (`CHANGELOG.md` + a stated semver
   commitment in the README) so 1.0 actually means something to consumers.
7. Everything past the M2 list (tries, balanced trees, skip lists,
   segment/Fenwick trees, the rest of DP/string/backtracking) is real and
   valuable but is **post-1.0 scope** — a `v1.x` roadmap, not a 1.0 blocker.

## Milestones

### M1 — Tooling & hygiene (blocking, do first)

- [ ] Add `.github/workflows/ci.yml`: `composer install`, `composer test`,
      `composer validate --no-check-all --strict`, `php -l` over `src/`.
      Matrix at least the PHP version(s) `composer.json` claims to support.
- [ ] Fix the 7 PHPUnit deprecations so `composer test` is clean.
- [ ] Resolve `src/SortingAlgorithms.php`: either delete it (it's a pure
      duplicate of `ArraySortAlgorythmes`'s selection sort) or mark it
      `@deprecated` with a `trigger_error(E_USER_DEPRECATED)` and a removal
      note in the new `CHANGELOG.md`. Deleting is cleaner pre-1.0 since no BC
      promise exists yet — do it now rather than carry it into 1.0.
- [ ] Add `phpstan` (or `psalm`) at a reasonable level, wire it into CI.
- [ ] Close the method-coverage gaps flagged in the last coverage run:
      `GraphDirectedCycleDetector` (50% methods), `DoublyLinkedList` /
      `SingleLinkedList` (~90% methods), `LevenshteinDistance` (88.9% methods),
      `DijkstraAlgorithm` (40% methods — `display()` untested, plus an
      untested branch each in `calculateDistances()` and
      `findShortestPath()`; see `articles/12-dijkstra.md`).

### M2 — Feature completeness (blocking)

Not "implement the whole backlog" — a small, deliberately curated set that
rounds out the categories a "data structures and algorithms" library is
expected to cover at all. As of 2026-08-08 every shipped structure was linear
(lists, stack, queue) plus one graph, and every shipped algorithm family
except Levenshtein was array-sorting/searching — too narrow a slice to call
1.0. Each item below closes a category gap, not just adds volume; the heap
item is now done (see below), the rest of this list is still open:

**Data structures**

- [ ] **Binary Search Tree** — `TODO.md` #6, `features.md` §2's `IBinaryTree`
      contract (insert/remove/contains, height, in/pre/post/level-order
      traversals). The first non-linear structure besides `Graph`; forces the
      immutable/persistent pattern (clone-then-splice, per `CLAUDE.md`) to
      prove out on a branching structure. Biggest single credibility jump.
      `src/DataStructure/Tree/BST/`, `tests/Unit/DataStructure/Tree/BST/BSTTest.php`.
- [x] **Binary Heap / Priority Queue** (min- and max-heap) — every algorithms
      book pairs this with BST and with Dijkstra below; also unblocks heap
      sort. `src/DataStructure/Heap/`. **Done** — `AbstractBinaryHeap` +
      `MinHeap`/`MaxHeap`, `IHeap` contract, custom-comparator support, plus a
      dedicated `PriorityQueue`/`PriorityQueueNode` (`IPriorityQueue`
      contract, backed by either heap via a required `PriorityQueueTypeEnum`
      constructor argument — value/priority wrapper) on top, tests in
      `tests/Unit/DataStructure/Heap/`, documented in the README and
      `articles/13-heap.md`. Heap sort itself (below) is still open.
- [ ] **Hash Table / Hash Map** — the single most conspicuous absence for a
      "data structures" library; pick one collision strategy (chaining is the
      simpler fit for this codebase's style) and document the choice.
      `src/DataStructure/HashTable/`.
- [ ] **Deque** (double-ended queue) — new `IDeque` contract per `TODO.md` #5;
      completes the queue family and gives a real backing for
      `IQueue::isFull()`-style bounded variants later.

**Algorithms**

- [ ] **Heap sort** — natural pairing now that the heap exists; extends
      `ArraySortAlgorythmes`.
- [x] **Dijkstra's shortest path** on `Graph` — BFS/DFS and cycle detection
      exist but couldn't answer "shortest path," one of the two questions
      people reach for a graph library to answer. **Done** —
      `DijkstraAlgorithm`/`DijkstraAlgorithmDistance`
      (`src/Algorithmes/DijkstraAlgorithm/`), built on
      `PriorityQueue(PriorityQueueTypeEnum::Min)` (its second real use after
      the heap itself), stateful unlike the rest of `Algorithmes/` (construct,
      call `calculateDistances()`, then `findShortestPath()`/`display()`),
      documented in the README and
      [`articles/12-dijkstra.md`](articles/12-dijkstra.md). Method coverage
      is 2/5 (40%) — `display()` and a couple of branches in
      `calculateDistances()`/`findShortestPath()` are untested; folded into
      the M1 coverage bullet above.
- [ ] **Topological sort** on `Graph` — still open; "valid build order" is
      the other question people reach for a graph library to answer.
- [ ] **One DP algorithm beyond edit distance** — 0/1 knapsack or LCS,
      whichever is smaller to implement well; proves DP is a supported
      category, not a one-off next to `LevenshteinDistance`.
- [ ] **One string algorithm** — KMP substring search; the other conspicuous
      category gap (searching currently only covers array search).

Follow existing conventions for all of the above: immutable/static-factory
style where a data structure, `ErrorMessages` constants for errors, contracts
per `features.md` §2, tests mirroring existing `tests/Unit/` layout.

Linked-list-backed Stack/Queue and circular-buffer queue (`TODO.md` #1–2) are
good next structures but genuinely not 1.0-blocking — they're re-implementations
of a shape (`IStack`/`IQueue`) already proven, not a new capability. Leave
those in the `v1.x` roadmap below.

### M3 — API stability contract (blocking)

- [x] Add `CHANGELOG.md` (Keep a Changelog format), backfill entries for
      `v0.1.0`/`v0.1.1`/`v0.2.0` from `git log`, going forward every release
      gets an entry. **Done** — root `CHANGELOG.md` now covers `v0.1.0`
      through `v0.3.0` as a single aggregate file, kept in sync with the
      existing `changelogs/*.md` per-version files (still live, gets a new
      file each release too).
- [ ] State a semver policy in the README: what counts as breaking for a
      library like this (removing/renaming a public method, changing a
      contract's shape, changing thrown-exception types) vs. non-breaking
      (adding a new structure/algorithm, adding an optional parameter).
- [ ] Do one pass reconciling `features.md` §5 ("Suggested next steps") —
      `TODO.md` already flags this as stale; fix it as part of this milestone
      since both docs need to agree before calling docs "done."

### M4 — Release

- [ ] Final `composer test` + CI green on `main`.
- [ ] Bump `composer.json` if it carries a version field; confirm Packagist
      picks up the tag.
- [ ] Tag `v1.0.0`, push, confirm it publishes on Packagist
      (packagist.org/packages/zack965/php-ds-algo).
- [ ] Update README badges (build status once CI exists, Packagist version).

## Suggested order

M1 → M2 → M3 → M4. M1 is pure risk-reduction and has no dependency on
anything else — do it first regardless of what else happens. M2 is the one
piece of new code on the critical path; everything else is process/docs.

## Explicitly out of scope for 1.0 (the `v1.x` roadmap)

Pulled forward from `TODO.md`/`features.md` so this doc doesn't duplicate
them — they remain valid, ranked backlog for *after* 1.0 ships:

- Linked-list-backed Stack/Queue, circular-buffer queue.
- Additional sorts (shell, counting, radix, bucket) — heap sort moved into M2.
- Dynamic/variable-size sliding window, monotonic-deque min/max window.
- Undirected-graph cycle detection, Bellman-Ford, Kruskal's/Prim's MST, A*
  — topological sort stays in M2 (still open); Dijkstra was in M2 and is
  now done, see above.
- Trie, Union-Find/Disjoint Set, AVL tree, Red-Black tree, Skip List,
  Segment Tree/Fenwick Tree — Hash Table moved into M2.
- Remainder of the DP family (memoized/tabulated Fibonacci, LIS, coin change
  — one of knapsack/LCS moved into M2), remainder of string algorithms
  (Rabin-Karp, palindrome family — KMP moved into M2), backtracking
  (N-Queens, subsets/permutations/combinations, Sudoku).

Keep ranking and re-deriving that list in `TODO.md` as work lands — this
document's job is only to draw the 1.0 line, not to own the long-term backlog.
