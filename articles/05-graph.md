# Graph

**Namespace:** `Zack\PhpDsAlgo\DataStructure\Graph`
**Classes:** `Graph`, `GraphEdge`, `GraphNode`
**Contract:** `Zack\PhpDsAlgo\Contracts\IGraph`

## What it is

A **graph** is a set of **nodes** (vertices) connected by **edges**. Graphs
model relationships: roads between cities, links between web pages,
dependencies between packages, friendships in a social network.

`Graph` is an **adjacency-list** graph:

- Nodes are plain `int` or `string` values, such as `'A'`, `42` or
  `'user:17'`.
- Edges are `GraphEdge` objects. Each holds a source, a destination, an
  optional **weight**, and an optional **metadata** array for labels or any
  extra data.
- A graph is **directed** by default. Pass `directed: false` for an
  undirected graph.
- A graph is **weighted** when its edges carry weights. The first edge you
  add sets this, and every later edge follows the same style, so the graph
  stays consistent.

## Building a graph

```php
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

$graph = new Graph(); // directed
$graph->addNode('A')->addNode('B')->addNode('C');

$graph->addEdge('A', 'B', 4);
$graph->addEdge('A', 'C', 1, ['label' => 'highway']);
$graph->addEdge('C', 'B', 2);
```

### Undirected graphs

`addEdge()` adds one edge, from source to destination. To model an undirected
connection, add both directions:

```php
$graph = new Graph(directed: false);
$graph->addNode('A')->addNode('B');
$graph->addEdge('A', 'B');
$graph->addEdge('B', 'A');
```

On an undirected graph, one call to `removeEdge('A', 'B')` removes the
connection in both directions.

### From an adjacency list

`buildFromAdjencyList()` builds the whole graph in one call. Nodes that only
appear as destinations are created for you:

```php
$graph->buildFromAdjencyList([
    'A' => [
        ['destination' => 'B', 'weight' => 4.5, 'metadata' => ['label' => 'road']],
        ['destination' => 'C', 'weight' => 2.0],
    ],
    'B' => [
        ['destination' => 'C', 'weight' => 1.0],
    ],
]);
```

In each row, only `destination` is required. `weight` defaults to `null`
and `metadata` to `[]`.

## API

### Nodes
```php
$graph->addNode($value);    // returns $this
$graph->hasNode($value);
$graph->getNode($value);    // returns the value, or throws NotFoundException
$graph->removeNode($value); // also removes every edge touching it
$graph->getNodes();
$graph->getNodesCount();
```

### Edges
```php
$graph->addEdge($from, $to, $weight = null, $metadata = []); // returns the GraphEdge
$graph->hasEdge($from, $to);
$graph->getEdge($from, $to);  // GraphEdge, or throws EdgeNotFoundException
$graph->removeEdge($from, $to);
$graph->getEdges();
$graph->getEdgeCount();
```

### Neighborhood queries
```php
$graph->getNeighbors($node);     // outgoing edges, O(1) lookup
$graph->getOutgoingEdges($node); // edges whose source is $node
$graph->getIncomingEdges($node); // edges whose destination is $node
$graph->getIncidentEdges($node); // both directions
```

### Graph properties & housekeeping
```php
$graph->isDirected();
$graph->isWeighted();
$graph->isEmpty();
$graph->clearEdges();
$graph->clearNodes();
$graph->clear();
$graph->getAdjency();      // raw adjacency list
```

### Visualization
```php
$graph->display();              // adjacency list drawn as a tree
$graph->getAdjencyMetrix();     // adjacency matrix as an array
$graph->printAdjacencyMatrix(); // adjacency matrix printed as a table
```

### Exceptions
| Situation | Exception |
|---|---|
| Adding a node that already exists | `DuplicateNodeException` |
| Looking up a missing node with `getNode()` | `NotFoundException` |
| Looking up a missing edge with `getEdge()` | `EdgeNotFoundException` |
| Using a node that does not exist, or mixing weighted and unweighted edges | `InvalidArgumentException` |

## Complexity

V = number of nodes, E = number of edges.

| Operation | Time |
|---|---|
| `addNode` / `hasNode` / `getNode` | O(V) |
| `addEdge` | O(V) |
| `getNeighbors` | O(1) |
| `hasEdge` / `getEdge` | O(degree of source) |
| `removeEdge` | O(degree) |
| `removeNode` | O(V + E) |
| `getOutgoingEdges` / `getIncomingEdges` / `getIncidentEdges` | O(V + E) |
| `getAdjencyMetrix` | O(V² · degree) |

Space: O(V + E). Adjacency lists stay compact for sparse graphs, where most
nodes connect to only a few others.

## Algorithms that work on Graph

- [BFS & DFS traversal](06-graph-traversal-bfs-dfs.md): reachability and
  visiting order
- [Directed cycle detection](07-graph-cycle-detection.md): find circular
  dependencies
- [Dijkstra](12-dijkstra.md): shortest weighted paths

## When to use it

- **Networks and maps:** routing, travel distances, network topology.
- **Dependencies:** build systems, package managers, task scheduling.
- **Social and recommendation data:** followers, friends, "people you may
  know".
- **State machines:** states as nodes, transitions as labeled edges (use the
  metadata array).
- Any problem that asks about **reachability, connectivity, paths or
  cycles**.

## When to choose something else

- **Strict parent/child hierarchy, where each item has one parent:**
  `BinaryTree` or `BinarySearchTree`.
- **Simple key → value associations with no relationships between items:**
  `HashMap`.
- **An ordered sequence:** a linked list, `Queue` or a plain array.
