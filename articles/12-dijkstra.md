# Dijkstra's Algorithm

**Namespace:** `Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm`
**Classes:** `DijkstraAlgorithm`, `DijkstraAlgorithmDistance`

## What it is

**Dijkstra's algorithm** finds the **shortest path by total weight** from one
source node to every other node in a weighted graph. Weights can be distance,
time, cost, or any non-negative quantity you want to minimize.

`DijkstraAlgorithm` works on any `IGraph`, such as [`Graph`](05-graph.md). It
uses a min-[`PriorityQueue`](13-heap.md) to always expand the closest
unfinished node next.

```php
use Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm\DijkstraAlgorithm;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

$graph = new Graph();
$graph->addNode('A')->addNode('B')->addNode('C')->addNode('D');
$graph->addEdge('A', 'B', 4);
$graph->addEdge('A', 'C', 1);
$graph->addEdge('C', 'B', 2);
$graph->addEdge('B', 'D', 1);
$graph->addEdge('C', 'D', 5);

$dijkstra = new DijkstraAlgorithm();
$dijkstra->calculateDistances($graph, 'A');

$dijkstra->findShortestPath('D'); // ['D', 'B', 'C', 'A']
$dijkstra->display();
```

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 Dijkstra Shortest Distances
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

● A      distance: 0        visited: yes  previous: -
● B      distance: 3        visited: yes  previous: C
● C      distance: 1        visited: yes  previous: A
● D      distance: 4        visited: yes  previous: B
```

The direct edge `A → B` costs 4, but `A → C → B` costs only 3. Dijkstra
finds the cheaper route even though it uses more edges.

## API

```php
$dijkstra = new DijkstraAlgorithm();

$dijkstra->calculateDistances(IGraph $graph, int|string $sourceNode);
$dijkstra->findShortestPath(int|string $targetNode); // path from target back to source
$dijkstra->display();                                // prints every node's distance and predecessor
```

- `calculateDistances()` runs the algorithm and stores the result on the
  instance.
- `findShortestPath()` returns the path **from the target back to the
  source**. Use `array_reverse()` for source-to-target order. A node that
  cannot be reached returns a path containing only itself.
- `display()` prints a readable table of every node's shortest distance,
  whether it was visited, and its previous node on the shortest path.
  Unreachable nodes show a distance of `∞`.

Each node's working state is a `DijkstraAlgorithmDistance` object with
`getShortestDistance()`, `getPreviousNode()`, `isVisited()` and
`getValue()`.

### Errors

| Situation | Exception |
|---|---|
| The source node is not in the graph | `RuntimeException("No node with this value")` |
| An edge the algorithm crosses has no numeric weight | `RuntimeException("the weight is not a numeric value")` |
| The target passed to `findShortestPath()` is unknown | `RuntimeException("Target node does not exist")` |

## How it works

1. Set every node's distance to ∞, except the source, which gets 0.
2. Put the source in a min-priority queue with priority 0.
3. Repeatedly take the node with the **smallest known distance** from the
   queue and mark it visited. Its distance is now final.
4. **Relax** each outgoing edge. If the path through the current node is
   shorter than the neighbor's known distance, update that distance, record
   the current node as the neighbor's predecessor, and add the neighbor to
   the queue with the new priority.
5. When a node is taken from the queue after it has already been finalized,
   skip it. This *lazy deletion* keeps the queue simple and the result
   correct.
6. When the queue is empty, every reachable node has its shortest distance
   and predecessor. `findShortestPath()` follows the predecessors back from
   the target.

## Complexity

| Operation | Time |
|---|---|
| `calculateDistances` | O(E log E), which is equivalent to O(E log V) |
| `findShortestPath` | O(length of the path) |
| `display` | O(V) |

Space: O(V + E) for the distance table and the priority queue.

## When to use it

- **Route planning:** shortest driving distance, fastest travel time,
  cheapest flight connections.
- **Network routing:** lowest-latency or lowest-cost paths between servers.
  Link-state protocols such as OSPF use Dijkstra.
- **Games:** pathfinding on weighted maps or terrain grids.
- **Logistics:** cheapest delivery routes and supply-chain paths.
- **Any "minimum total cost" question** over a graph with non-negative
  costs.

## When to choose something else

- **Unweighted graphs, or when every edge costs the same:**
  [BFS](06-graph-traversal-bfs-dfs.md) finds the fewest-edge path directly.
- **Negative edge weights** (refunds, gains): Dijkstra assumes weights are
  non-negative. Use Bellman-Ford for graphs with negative weights.
- **Only checking whether a path exists:**
  [DFS or BFS](06-graph-traversal-bfs-dfs.md).
