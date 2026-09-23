# Graph Traversal: BFS & DFS

**Namespace:** `Zack\PhpDsAlgo\Algorithmes`
**Classes:** `GraphBreadthFirstTraversal`, `GraphDepthFirstTraversal`

## What it is

Graph traversal visits every node you can reach from a starting node. The
two basic strategies differ in the **order** they visit nodes:

- **Breadth-First Search (BFS)** goes **level by level**. It visits every
  neighbor of the start node, then their neighbors, and so on outward.
- **Depth-First Search (DFS)** goes **as deep as possible** down one path
  before it backtracks to try another.

Both classes are static utilities. They accept any `IGraph` implementation
and return the visited nodes in order.

```php
use Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal;
use Zack\PhpDsAlgo\Algorithmes\GraphDepthFirstTraversal;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

$graph = new Graph();
foreach (['A', 'B', 'C', 'D', 'E'] as $n) {
    $graph->addNode($n);
}
$graph->addEdge('A', 'B');
$graph->addEdge('A', 'C');
$graph->addEdge('B', 'D');
$graph->addEdge('C', 'E');

GraphBreadthFirstTraversal::traverse($graph, 'A'); // ['A', 'B', 'C', 'D', 'E']
GraphDepthFirstTraversal::traverse($graph, 'A');   // ['A', 'C', 'E', 'B', 'D']
```

If the start node is not in the graph, both methods throw
`NotFoundException`.

## How BFS works

1. Mark the start node visited and put it in a queue.
2. Take the node at the front of the queue.
3. Mark each unvisited neighbor visited and add it to the back of the queue.
4. Repeat until the queue is empty.

A node is marked visited **when it is enqueued**, so it enters the queue
only once. BFS reaches nodes in order of distance from the start, counted in
edges.

## How DFS works

1. Push the start node onto a stack.
2. Pop a node. If it has not been visited yet, mark it visited and push its
   unvisited neighbors.
3. Repeat until the stack is empty.

The implementation is **iterative**. It uses an explicit stack rather than
recursion, so it handles long paths and deep graphs without running into
PHP's recursion limits.

## Complexity

| | BFS | DFS |
|---|---|---|
| Helper structure | queue (FIFO) | stack (LIFO) |
| Node marked visited | when enqueued | when popped |
| Recursion | none | none |
| Result | nodes in order of distance from the start | nodes in depth-first order |

Both run in O(V + E) in the classic formulation, and use O(V) space for the
visited list and the helper structure.

## When to use BFS

- **Fewest-hops paths** in unweighted graphs: degrees of separation in a
  social network, the minimum number of moves in a puzzle.
- **Level-by-level processing:** org charts, "friends of friends",
  spreading notifications ring by ring.
- **Nearest match first:** finding the closest node that satisfies a
  condition.
- **Web crawling** to a limited depth.

## When to use DFS

- **Reachability:** "can I get from A to B?"
- **Exploring every path:** maze solving, puzzle search, generating
  combinations.
- **Connected components:** grouping nodes that belong together.
- **The basis for other graph algorithms**, such as cycle detection and
  topological ordering. See [Directed Cycle Detection](07-graph-cycle-detection.md).

## When to choose something else

- **Shortest path by total weight** (distance, cost, time):
  [Dijkstra](12-dijkstra.md) accounts for edge weights. BFS counts only
  edges.
- **Only checking whether a directed graph has a cycle:**
  [`GraphDirectedCycleDetector`](07-graph-cycle-detection.md) answers that in
  one call.
