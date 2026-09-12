# TODO

Backlog of what's actually left to implement, ordered smallest-effort-first.
Reconciled against `src/` as of 2026-08-22 (adds `HashSet`/`IHashSet`; `Set`
gained `get()`/`indexOf()`/`update()`; `GeneralArrayAlgorithms` gained
`equals()`; `getBucket()`/`getBuckets()` were removed from `HashTable`/
`HashMap`/`HashSet` for exposing internal storage; closures are now
consistently rejected by all three hash structures); reconciled again as of
2026-09-12 (`Deque`, `BinaryTree`/`BinarySearchTree`, `CircularLinkedList`
all landed with full test suites; heap sort and bucket sort finished
`ArraySortAlgorythmes`; KMP closed the string-matching gap; see each entry
below for specifics, and `missing.md`/`PathToOnePointO.md` for the fuller
writeups including the bugs found and fixed along the way) — see
`features.md` for conventions each new structure/algorithm must follow
(immutable style, `ErrorMessages`, contracts, test layout — note `features.md`
§1 itself now flags that "immutable style" turned out to only ever apply to
the linked lists in practice, not a rule to follow for new non-list
structures).

## Already implemented (not action items — listed so this doesn't re-flag them)

- `SingleLinkedList` / `DoublyLinkedList`, both with `insertBefore` / `insertAfter`
- `CircularLinkedList` (`src/DataStructure/LinkedList/Single/`) — singly-linked,
  same persistent/clone-then-splice pattern and method surface as
  `SingleLinkedList` (reuses `SingleLinkedListNode`), but the tail's `next`
  wraps back to the head instead of `null`. A doubly-linked circular variant
  is still not implemented. 90.62% method / 99.07% line coverage. A real bug
  was found writing its tests: `insert()` computed the new tail by walking
  the *old* length instead of the new (one-longer) one for a genuine middle
  insert, so the returned list's tracked tail was wrong by one node (though
  the chain itself, walked by hand, was still correct) — fixed, and
  `insertBeforeNode()`/`insertAfterNode()` (which delegate to `insert()`)
  along with it. See `articles/01-single-linked-list.md`.
- `ArrayStack` (array-backed stack)
- `Queue` (array-backed queue) — `maxCapacity` now defaults to `PHP_INT_MAX`
  with real validation (constructor and `setMaxCapacity()` both reject a
  capacity smaller than the current/initial item count), replacing an
  earlier design where the uninitialized-by-default typed property threw a
  bare PHP `Error` if read before an explicit `setMaxCapacity()` call.
- `Deque` (`src/DataStructure/Queue/Deque.php`, `IDeque` contract) — extends
  `Queue`, adding `enqueueFront()` (doesn't check `isFull()`, unlike
  inherited `enqueue()`) and `dequeueTail()` (returns `null` instead of
  throwing on empty, unlike inherited `dequeue()`). 100% method/line
  coverage. See `articles/04-queue.md`.
- `Graph` + `GraphNode` / `GraphEdge` (directed/undirected, weighted/unweighted, adjacency list,
  adjacency matrix via `getAdjencyMetrix()` / `printAdjacencyMatrix()`)
- `MinHeap` / `MaxHeap` (array-backed binary heap over `AbstractBinaryHeap` +
  the `IHeap` contract; custom-comparator support, capacity growth). Mutable,
  like `ArrayStack`/`Queue`/`Graph` — no static factories, construct with `new`.
- `PriorityQueue` / `PriorityQueueNode` (`src/DataStructure/Heap/`,
  `IPriorityQueue` contract) — value/priority wrapper around an internal
  `MaxHeap` or `MinHeap`, selected via a required `PriorityQueueTypeEnum`
  constructor argument (`Max` = higher priority extracts first, `Min` =
  lower priority extracts first). Mutable, no static factories, same shape
  as `MinHeap`/`MaxHeap`.
- Sorting: bubble, selection, insertion, merge, quick, heap, bucket — the
  last two closed the M2 heap-sort gap (`heapSort()`, backed by `MinHeap`)
  and shipped alongside it (`bucketSort()`, not originally scoped for M2;
  rejects any element that isn't `int`/`float` with `InvalidArgumentException`,
  checked up front before any bucketing work runs). `ArraySortAlgorythmes` is
  now 100% method/line covered end to end.
- Searching: binary, exponential, interpolation, jump, linear, ternary, fibonacci
- String matching: KMP (`Zack\PhpDsAlgo\Algorithmes\Strings\KMP` —
  `calculateLspTable()` + `run()`, 100% method/line coverage). See
  `articles/17-kmp.md`.
- Sliding window: fixed-size
- Graph traversal: BFS, DFS
- Graph cycle detection: directed (`GraphDirectedCycleDetector`)
- `GeneralArrayAlgorithms`: `hasDuplicates`, `contains`, `remove`, `equals` (the last is a
  public NaN-aware equality helper — strict `===`, except two `NAN` floats compare equal to
  each other — shared by `contains()`/`remove()` internally and called directly by
  `Set::indexOf()`/`HashSet::update()`; without it a stored `NAN` could be found by
  `contains()`/`hasValue()` yet never actually matched and removed by `remove()`/`delete()`)
- `LevenshteinDistance` (edit distance, Wagner-Fischer DP, with reconstructed optimal path)
- `DijkstraAlgorithm` / `DijkstraAlgorithmDistance` (`src/Algorithmes/DijkstraAlgorithm/`) — single-source
  shortest paths, backed by `PriorityQueue(PriorityQueueTypeEnum::Min)`. Stateful (construct, call
  `calculateDistances()`, then `findShortestPath()`/`display()`), unlike the rest of `Algorithmes/`.
  Method coverage is currently 2/5 — `display()` and a couple of branches in `calculateDistances()`/
  `findShortestPath()` are untested; see `articles/12-dijkstra.md`'s "Known gap" section.
- `HashTable` (`src/DataStructure/HashTabe/`, `IHashTable` contract — note the folder is
  `HashTabe`, a genuine typo, not one of the intentional `Algorythmes`-style misspellings) —
  separate-chaining hash table (really a hash *set*, since values act as their own keys),
  generic over any hashable value (`@template T`; strings hash directly, other
  scalars/null/arrays/objects via `serialize()`, `-0.0` canonicalized to `0.0` so it matches
  `0.0` per PHP's `===`, closures/resources rejected with `InvalidArgumentException` —
  closure-rejection was previously broken, silently accepting closures via an object-identity
  shortcut that bypassed `serialize()`'s natural rejection; now fixed with an explicit
  `instanceof Closure` check). No static factories, construct with
  `new HashTable(int $capacity = 10)`. Mutable, auto-resizes (doubles capacity) once load
  factor exceeds 0.7. Implements `IteratorAggregate`/`Countable` (`foreach`/`count()` work
  directly). No `getBucket()`/`getBuckets()` (removed — they exposed the raw bucket array;
  `getValuePosition()` still reports which bucket a value is in). 100% method/line coverage.
- `HashMap` / `HashMapNode` (`src/DataStructure/HashTabe/`, `IHashMap` contract) — proper
  key-value map living alongside `HashTable`, sharing its hashing approach but keyed
  independently of the stored value; entries are `HashMapNode` value objects
  (`getKey()`/`getValue()`/`setValue()`). `put()` upserts (inserts or replaces in place);
  `update()` is the must-already-exist counterpart, returning `false` instead of inserting
  when the key is missing; `get()` returns `null` for a missing key rather than throwing.
  Same generic-key rules as `HashTable`'s values (values themselves are unrestricted — a
  closure/resource is a valid *value*, just not a valid *key*; closure-key rejection now has
  an explicit check/message too, matching `HashTable`/`HashSet`, though it already worked
  correctly before via `serialize()`'s natural failure). No static factories, construct with
  `new HashMap(int $capacity = 10)`. Mutable, same auto-resize behavior as `HashTable`, also
  implements `IteratorAggregate`/`Countable`. No `getBucket()`/`getBuckets()` (removed, same
  as `HashTable` — `getKeyPosition()` still reports bucket placement). 100% method/line
  coverage.
- `HashSet` (`src/DataStructure/HashTabe/`, `IHashSet` contract) — hashed sibling of `Set`:
  each bucket is a real `Set` instance instead of a raw array, so unlike `HashTable` (a bag),
  `HashSet` is a genuine set — inserting an equal value twice is a no-op. Generic with a
  narrower bound than `HashTable`/`HashMap` (`@template T of scalar|object` — `null` and
  arrays are rejected, not just closures/resources). Objects (including closures, which are
  explicitly rejected before reaching this path) are hashed by `spl_object_id()` (identity),
  not `serialize()` (value) like `HashTable`/`HashMap` — membership is still always `===`
  either way, so this only changes which bucket a lookup starts scanning. `-0.0`/`0.0`
  canonicalized same as `HashTable`. Shares `Set`'s NaN-equal-to-NaN carve-out via
  `GeneralArrayAlgorithms::equals()` (used by `Set::indexOf()`, which `getValuePosition()`
  and `update()` both call), so a stored `NAN` is reliably findable *and* removable.
  `update()` never throws — an unhashable `$oldValue`/`$newValue` (or a no-op rename) just
  returns `false`/`true` instead. No static factories, construct with
  `new HashSet(int $capacity = 10)`. Mutable, same auto-resize behavior as `HashTable`, also
  implements `IteratorAggregate`/`Countable`. No `getBucket()`/`getBuckets()` — deliberately
  never added (unlike `HashTable`/`HashMap`, where they existed and were later removed):
  exposing a bucket here would mean exposing the actual internal `Set` instance, letting a
  caller mutate the table directly and bypass `tableHash()` entirely. Documented in the
  README under [HashSet](README.md#hashset).
- `Set` (`src/DataStructure/Set/`, `ISet` contract) — plain array-backed collection of
  unique values (`@template T`), de-duplicated via strict `===` comparison (except two `NAN`
  floats, which count as equal to each other — see `GeneralArrayAlgorithms::equals()` above),
  no hashing (a linear scan, unlike `HashTable`). Also has indexed access — `get(int $index)`
  (throws `OutOfBoundsException` if out of range), `indexOf(mixed $value): int|false`, and
  `update(mixed $oldValue, mixed $newValue): bool` (replaces in place, only if `$oldValue`
  exists and `$newValue` doesn't already exist elsewhere) — alongside set-algebra methods
  (`union`, `intersection`, `difference`, `isSubsetOf`, `isSupersetOf`, `equals`).
  `union()`/`intersection()`/`difference()` are pure (return a new `Set`, never mutate either
  operand), while `add()`/`remove()`/`clear()`/`update()` mutate in place — the one structure
  in this library mixing both styles. No static factories, construct with
  `new Set(array $data = [])`. `null` is rejected (throws `InvalidArgumentException`) as an
  element. Implements `IteratorAggregate`/`Countable`. 100% method/line coverage. Not the
  same thing as the still-pending Disjoint Set/Union-Find item further down this file —
  that's a different structure (find/union-by-rank over partitioned sets), this is a plain
  unique-value collection. `HashSet` (below) is its hashed sibling, using a `Set` per bucket.
  Documented in the README under [Set](README.md#set) and in
  [`articles/15-set.md`](articles/15-set.md).
- `BinaryTree` / `BinarySearchTree` (`src/DataStructure/Tree/`, over a shared
  `AbstractTree` base and `BinaryTreeNode`; `ITree`/`IBinaryTree`/
  `IBinarySearchTree` contracts) — the first non-linear structures besides
  `Graph`. **Mutable**, like `ArrayStack`/`Queue`/the heaps — not the
  clone-then-splice persistent pattern the linked lists use, despite being
  tree-shaped. `BinaryTree`: level-order `insert()`/`remove()`/`search()`,
  `isFull()`/`isComplete()`/`isPerfect()`/`isBalanced()`, `getDiameter()`
  (allows duplicate values). `BinarySearchTree`: ordered `insert()`/
  `remove()`/`search()` (duplicates ignored), `min`/`max`,
  `predecessor`/`successor`, `floor`/`ceiling`/`findClosest`,
  `rangeSearch`/`countInRange`, `kthSmallest`/`kthLargest`,
  `lowestCommonAncestor`, `isValid()`, and a genuine Day-Stout-Warren
  `balance()` (not AVL rotation). 72.22%/95.91% method/line coverage on
  `BinaryTree`, 82.76%/95.45% on `BinarySearchTree`, 100%/100% on
  `BinaryTreeNode`. Two blocking issues and three logic bugs surfaced while
  writing the test suite, all fixed:
  - The plain-tree files were misspelled on disk (`BinarryTree.php`,
    `IBinarryTree.php`) while declaring correctly-spelled `BinaryTree`/
    `IBinaryTree` classes — a PSR-4 mismatch that made both entirely
    unreachable via normal autoloading. Renamed to match; no code changed.
  - Every BFS-based method across `AbstractTree`/`BinaryTree`/
    `BinarySearchTree` built a bare `new Queue()` without ever setting a
    capacity, crashing under `Queue`'s old uninitialized-by-default
    `$maxCapacity` (see the `Queue` entry above — fixed there, once, rather
    than at each of the 8 call sites).
  - `BinarySearchTree::isValid()` crashed with a `TypeError` on any
    non-empty tree (a private recursive helper's parameter was missing a
    `?` it needed).
  - `BinaryTree::isBalanced()` always returned `false` (a height-tracking
    recursion reused one value to mean both "this child is empty" and "an
    imbalance was found downstream").
  - `BinaryTree::getDiameter()` was off by one edge (needed
    `leftHeight + rightHeight + 2`, not `+ 1`).

  Documented in the README under
  [BinaryTree & BinarySearchTree](README.md#binarytree--binarysearchtree)
  and in [`articles/16-binary-search-tree.md`](articles/16-binary-search-tree.md)
  (full root-cause writeups for all three logic bugs).

## Next up (recommended order)

1. **Linked-list-backed Stack / Queue** — parity variants of the existing
   array-backed `ArrayStack`/`Queue`, built on `SingleLinkedList`. Low effort,
   no new interface needed (reuse `IStack`/`IQueue`).
2. **Circular (ring-buffer) Queue** — fixed-capacity queue backed by an array
   with wraparound indices; gives real meaning to `IQueue::isFull()`. (Not
   to be confused with `CircularLinkedList`, already done — see above.)
3. **Sorting**: shell sort, counting sort, radix sort. (Heap sort and bucket
   sort are done — see above.)
4. **Sliding window**: variable-size/dynamic window (grow/shrink on a
   predicate), monotonic-deque-based window max/min.
5. **Graph algorithms beyond BFS/DFS** (build on existing `Graph`): cycle
   detection for undirected graphs (directed is done — see
   `GraphDirectedCycleDetector`), topological sort, Bellman-Ford,
   Kruskal's / Prim's MST, A*. (Dijkstra is done — see `DijkstraAlgorithm`
   above, built on `PriorityQueue(PriorityQueueTypeEnum::Min)`.)
6. **Trie**, **Union-Find / Disjoint Set**, **AVL tree**, **Red-Black tree**,
    **Skip List**, **Segment Tree** / **Fenwick Tree**. (Hash Table and Hash
    Map are done — see `HashTable`/`HashMap` above. A plain unique-value
    `Set` is also done — see `Set` above — but that's not the same as
    Union-Find/Disjoint Set, which is still open. A plain `BinarySearchTree`
    is also done — see above — AVL/Red-Black are the *balanced* trees still
    open here.)
7. **Algorithm categories not started yet**:
    - Dynamic programming: Fibonacci (memoized vs tabulated), LCS, LIS, 0/1
      knapsack, coin change. (Edit distance is done — see `LevenshteinDistance`
      above.)
    - String algorithms: naive substring search, Rabin-Karp, palindrome
      checks / longest palindromic substring. (KMP is done — see above.)
    - Backtracking: N-Queens, subsets/permutations/combinations, Sudoku solver.
    - Two pointers: two-sum on sorted array, in-place dedup, container-with-
      most-water style problems.
    - Recursion / divide & conquer: Tower of Hanoi, fast exponentiation
      (power by squaring).

## Infra / non-code

- No `.github/workflows/` yet — no CI running `composer test` on push/PR.
- `v0.1.0` is already tagged and published on Packagist
  (packagist.org/packages/zack965/php-ds-algo) as of 2026-07-21.
- `features.md` §5 ("Suggested next steps") is still stale — it lists
  insert-before/after, Stack/Queue, and merge/quick sort as pending, which
  are all done; only its item 4 (Binary Search Tree) has been reconciled so
  far (now marked done, pointing at §3). Still needs a full pass to reflect
  everything actually shipped and re-rank against this file.