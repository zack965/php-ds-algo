# TODO

Backlog of what's actually left to implement, ordered smallest-effort-first.
Reconciled against `src/` as of 2026-08-05 — see `features.md` for conventions
each new structure/algorithm must follow (immutable style, `ErrorMessages`,
contracts, test layout).

## Already implemented (not action items — listed so this doesn't re-flag them)

- `SingleLinkedList` / `DoublyLinkedList`, both with `insertBefore` / `insertAfter`
- `ArrayStack` (array-backed stack)
- `Queue` (array-backed queue)
- `Graph` + `GraphNode` / `GraphEdge` (directed/undirected, weighted/unweighted, adjacency list,
  adjacency matrix via `getAdjencyMetrix()` / `printAdjacencyMatrix()`)
- Sorting: bubble, selection, insertion, merge, quick
- Searching: binary, exponential, interpolation, jump, linear, ternary, fibonacci
- Sliding window: fixed-size
- Graph traversal: BFS, DFS
- Graph cycle detection: directed (`GraphDirectedCycleDetector`)
- `GeneralArrayAlgorithms`: `hasDuplicates`, `contains`
- `LevenshteinDistance` (edit distance, Wagner-Fischer DP, with reconstructed optimal path)

## Next up (recommended order)

1. **Linked-list-backed Stack / Queue** — parity variants of the existing
   array-backed `ArrayStack`/`Queue`, built on `SingleLinkedList`. Low effort,
   no new interface needed (reuse `IStack`/`IQueue`).
2. **Circular (ring-buffer) Queue** — fixed-capacity queue backed by an array
   with wraparound indices; gives real meaning to `IQueue::isFull()`.
3. **Sorting**: heap sort, shell sort, counting sort, radix sort, bucket sort.
4. **Sliding window**: variable-size/dynamic window (grow/shrink on a
   predicate), monotonic-deque-based window max/min.
5. **Deque** (double-ended queue) — new `IDeque` contract.
6. **Binary Search Tree** — first non-linear structure besides `Graph`; forces
   an `IBinaryTree` contract (insert/remove/contains + in/pre/post/level-order
   traversals, per `features.md` §2).
7. **Priority Queue / Binary Heap** (min-heap and max-heap).
8. **Graph algorithms beyond BFS/DFS** (build on existing `Graph`): cycle
   detection for undirected graphs (directed is done — see
   `GraphDirectedCycleDetector`), topological sort, Dijkstra, Bellman-Ford,
   Kruskal's / Prim's MST, A*.
9. **Trie**, **Union-Find / Disjoint Set**, **Hash Table** (chaining or open
    addressing), **AVL tree**, **Red-Black tree**, **Skip List**,
    **Segment Tree** / **Fenwick Tree**.
10. **Algorithm categories not started yet**:
    - Dynamic programming: Fibonacci (memoized vs tabulated), LCS, LIS, 0/1
      knapsack, coin change. (Edit distance is done — see `LevenshteinDistance`
      above.)
    - String algorithms: naive substring search, KMP, Rabin-Karp, palindrome
      checks / longest palindromic substring.
    - Backtracking: N-Queens, subsets/permutations/combinations, Sudoku solver.
    - Two pointers: two-sum on sorted array, in-place dedup, container-with-
      most-water style problems.
    - Recursion / divide & conquer: Tower of Hanoi, fast exponentiation
      (power by squaring).

## Infra / non-code

- No `.github/workflows/` yet — no CI running `composer test` on push/PR.
- `v0.1.0` is already tagged and published on Packagist
  (packagist.org/packages/zack965/php-ds-algo) as of 2026-07-21.
- `features.md` §5 ("Suggested next steps") is stale — it still lists
  insert-before/after, Stack/Queue, and merge/quick sort as pending; needs a
  pass to reflect what's actually shipped and re-rank against this file.