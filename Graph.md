# Graph

A persistent and immutable graph implementation supporting both **directed** and **undirected** graphs, as well as **weighted** and **unweighted** edges.

Every operation that modifies the graph returns a **new instance**, preserving the original graph.

---

# Features

- Immutable / Persistent
- Directed Graph
- Undirected Graph
- Weighted Graph
- Unweighted Graph
- Adjacency List representation
- Adjacency Matrix export
- Iterator support
- Functional operations
- Factory methods
- PHPUnit tested

---

# Interface

```php
interface IGraph
{
    // Metadata
    public function getNodeCount(): int;
    public function getEdgeCount(): int;

    public function isDirected(): bool;
    public function isWeighted(): bool;

    // Nodes
    public function addNode(mixed $value): self;

    public function removeNode(mixed $value): self;

    public function containsNode(mixed $value): bool;

    public function getNode(mixed $value): GraphNode;

    // Edges
    public function addEdge(
        mixed $from,
        mixed $to,
        ?float $weight = null
    ): self;

    public function removeEdge(
        mixed $from,
        mixed $to
    ): self;

    public function hasEdge(
        mixed $from,
        mixed $to
    ): bool;

    public function getEdge(
        mixed $from,
        mixed $to
    ): ?GraphEdge;

    // Adjacency
    public function neighborsOf(mixed $value): array;

    public function degreeOf(mixed $value): int;

    // Traversals
    public function bfs(mixed $start): array;

    public function dfs(mixed $start): array;

    // Conversion
    public function toAdjacencyList(): array;

    public function toAdjacencyMatrix(): array;

    // Functional
    public function map(callable $callback): self;

    public function filter(callable $callback): self;

    // Utilities
    public function clear(): self;

    // Iterator
    public function getIterator(): Traversable;
}
```

---

# Specifications

## Internal Representation

- Adjacency List (primary)
- Adjacency Matrix (generated on demand)

## Supported Graph Types

- Directed Graph
- Undirected Graph
- Weighted Graph
- Unweighted Graph
- Cyclic Graph
- Acyclic Graph (DAG)

## Time Complexity

| Operation | Complexity |
|-----------|------------|
| Add Node | O(1) |
| Remove Node | O(V + E) |
| Add Edge | O(1) |
| Remove Edge | O(E) |
| Has Edge | O(degree) |
| Neighbors | O(degree) |
| BFS | O(V + E) |
| DFS | O(V + E) |

---

# Planned Graph Algorithms

## Traversal

- Breadth-First Search (BFS)
- Depth-First Search (DFS)

## Shortest Path

- Dijkstra
- Bellman-Ford
- Floyd-Warshall
- A*

## Minimum Spanning Tree

- Prim
- Kruskal

## Topological Algorithms

- Topological Sort

## Connectivity

- Connected Components
- Strongly Connected Components (Kosaraju)
- Tarjan's Algorithm

## Cycle Detection

- Directed Graph Cycle Detection
- Undirected Graph Cycle Detection

## Path Finding

- All Paths Between Two Nodes
- Shortest Unweighted Path
- Bidirectional Search

## Network Flow

- Ford-Fulkerson
- Edmonds-Karp

## Matching

- Bipartite Graph Detection
- Maximum Bipartite Matching

## Miscellaneous

- Graph Transpose
- Graph Clone
- Eulerian Path / Circuit
- Hamiltonian Path / Circuit
- Articulation Points
- Bridges Detection
- Graph Coloring