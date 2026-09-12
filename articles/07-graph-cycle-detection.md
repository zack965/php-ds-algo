# GraphDirectedCycleDetector: DFS + recursion-stack cycle detection

`Zack\PhpDsAlgo\Algorithmes\GraphDirectedCycleDetector::detect(IGraph
$graph): bool` answers "does this directed graph contain a cycle?" using the
classic recursive-DFS-with-a-recursion-stack technique — the standard
approach for directed-cycle detection (distinct from undirected-cycle
detection, which this class does **not** claim to handle; see the caveat
below).

## The algorithm

```php
public static function detect(IGraph $graph): bool
{
    $visited = [];
    $recursionStack = [];
    $adjency = $graph->getAdjency();
    foreach ($adjency as $node => $edges) {
        if (!in_array($node, $visited) && self::traverse($graph, $node, $visited, $recursionStack)) {
            return true;
        }
    }
    return false;
}
```

Because a graph can be disconnected (or have multiple independent
components/roots), `detect()` doesn't just DFS from one arbitrary start —
it iterates over **every** node in the adjacency list and kicks off a fresh
DFS from any node not yet visited by a prior traversal. This is what makes
it correct on graphs that aren't fully reachable from a single node.

## The recursive traversal — two arrays, two different meanings

```php
private static function traverse(IGraph $graph, int|string $start, array &$visited, array &$recursionStack): bool
{
    $visited[] = $start;
    $recursionStack[] = $start;
    foreach ($graph->getNeighbors($start) as $neighbor) {
        $destination = $neighbor->getDestinationNode();
        if (GeneralArrayAlgorithms::contains($recursionStack, $destination)) {
            return true; // back edge -> cycle
        }
        if (!GeneralArrayAlgorithms::contains($visited, $destination)
            && self::traverse($graph, $destination, $visited, $recursionStack)) {
            return true;
        }
    }
    array_pop($recursionStack);
    return false;
}
```

The key idea that makes this work for *directed* graphs specifically:

- **`$visited`** — every node ever explored, across the *whole* `detect()`
  call (never shrinks). Prevents re-exploring a node's whole subtree
  redundantly on later top-level iterations in `detect()`'s loop.
- **`$recursionStack`** — only the nodes on the **current DFS path** from
  the current root down to wherever the recursion currently is. A node is
  pushed onto it on entry and popped off (`array_pop`) right before
  `traverse()` returns for that node — i.e. it behaves like an actual call
  stack, mirrored in an array specifically so it can be searched.

A cycle exists **iff** a DFS ever reaches a neighbor that's already on the
*current recursion path* — that's a **back edge**, pointing from a
descendant back up to one of its own ancestors in the DFS tree. Reaching an
already-`$visited`-but-not-on-the-current-path node is *not* a cycle — it's
a **cross edge** (common in DAGs, e.g. a "diamond" shape: `A→B`, `A→C`,
`B→D`, `C→D` visits `D` twice but has no cycle) — which is exactly why two
separate arrays are needed instead of one. Using only `$visited` (as a
naive DFS-reachability check would) gives false positives on any DAG with a
shared descendant; `$recursionStack` is what disambiguates "this node is an
ancestor of itself" from "this node was already fully explored elsewhere."

## Why this only works for directed graphs

On an **undirected** graph, the edge you just came from (`parent → current`)
is trivially "already on the recursion stack" — walking back along the same
edge you arrived on would always look like a cycle. Undirected-cycle
detection needs to track and explicitly skip the immediate parent edge,
which this implementation doesn't do. The class name (`GraphDirectedCycleDetector`)
and this project's own roadmap (`PathToOnePointO.md` lists "Undirected-graph
cycle detection" as explicitly out-of-scope/future work) both confirm this
is a deliberate, documented scope boundary rather than an oversight — don't
call `detect()` on an undirected `Graph` expecting a meaningful answer.

## Complexity

Each node is pushed/popped from `$recursionStack` exactly once per
top-level DFS root, and each edge is examined once — nominally O(V+E).
`GeneralArrayAlgorithms::contains()` is a linear scan (same caveat as
[`06-graph-traversal-bfs-dfs.md`](06-graph-traversal-bfs-dfs.md)), so the
two `contains()` calls per edge push the practical cost toward O(V·E) on
graphs where `$visited`/`$recursionStack` grow large — again, fine at the
scale this library targets, but not the asymptotically optimal
hash-set-backed version you'd write for a large graph.
