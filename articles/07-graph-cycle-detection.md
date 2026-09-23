# Directed Cycle Detection

**Namespace:** `Zack\PhpDsAlgo\Algorithmes`
**Class:** `GraphDirectedCycleDetector`

## What it is

A **cycle** in a directed graph is a path that starts and ends at the same
node by following edge directions: `A → B → C → A`. A directed graph with no
cycles is a **DAG** (Directed Acyclic Graph).

`GraphDirectedCycleDetector::detect()` answers one question: *does this
directed graph contain a cycle?*

```php
use Zack\PhpDsAlgo\Algorithmes\GraphDirectedCycleDetector;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

$graph = new Graph(); // directed
foreach (['A', 'B', 'C'] as $n) {
    $graph->addNode($n);
}
$graph->addEdge('A', 'B');
$graph->addEdge('B', 'C');

GraphDirectedCycleDetector::detect($graph); // false, a DAG

$graph->addEdge('C', 'A');
GraphDirectedCycleDetector::detect($graph); // true, A → B → C → A
```

## How it works

The detector runs a **depth-first search** and keeps two records:

- **Visited**: every node explored so far. The detector explores each node
  only once.
- **Recursion stack**: only the nodes on the **current path** from the DFS
  root to the node being explored. A node joins this stack when the search
  enters it and leaves when the search has finished exploring it.

A cycle exists exactly when the search reaches a neighbor that is **already
on the current path**. This is a *back edge*: it points from a node back to
one of its own ancestors.

Reaching a node that was visited earlier on a *different* path is not a
cycle. For example, a "diamond" (`A→B`, `A→C`, `B→D`, `C→D`) reaches `D`
twice but has no cycle. Keeping a separate recursion stack is what lets the
detector tell the two situations apart.

`detect()` starts a fresh search from every node not yet visited, so it
checks every part of the graph, including components that no single start
node can reach.

## Complexity

- **Time:** O(V + E) in the classic formulation. Each node is entered once
  and each edge is examined once.
- **Space:** O(V) for the visited record and the recursion stack.

## When to use it

- **Dependency validation:** make sure packages, modules or build targets do
  not depend on each other in a loop.
- **Task scheduling:** confirm that a set of tasks with "must run before"
  rules can actually run in some order.
- **Workflow and state-machine validation:** spot loops in approval chains or
  pipelines.
- **Before topological ordering:** a valid order exists only when the graph
  has no cycles.
- **Deadlock detection:** find circular waits in resource-allocation graphs.

## When to choose something else

- **Undirected graphs:** this detector is built for directed graphs, where
  edge direction defines a cycle. Use it on graphs created with the default
  `new Graph()`.
- **Listing reachable nodes rather than asking yes/no:** use
  [BFS or DFS traversal](06-graph-traversal-bfs-dfs.md).
