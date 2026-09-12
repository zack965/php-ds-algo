# Graph: adjacency-list graph with directed/weighted flags

`Zack\PhpDsAlgo\DataStructure\Graph\Graph` (`final`, implements `IGraph`) is
a mutable adjacency-list graph. Nodes are bare `int|string` values (no
wrapper object required to use the graph, though `GraphNode` exists — see
below); edges are `GraphEdge` objects carrying source, destination, an
optional weight, and an optional metadata array.

## Internal representation

```php
private array $nodes;      // list<int|string>
private array $adjacency;  // array<int|string, GraphEdge[]>
```

`$nodes` is just the flat list of node values added so far (used for
existence checks). `$adjacency` is the real structure: each key is a node
value, each value is the list of `GraphEdge`s **leaving** that node.
`addNode($value)` pushes onto `$nodes` and seeds `$adjacency[$value] = []`;
`addEdge($source, $destination, $weight, $metadata)` requires both endpoints
to already exist (`CheckNodesExists()` throws `InvalidArgumentException`
otherwise) and appends a new `GraphEdge` to `$adjacency[$source]`.

`GraphNode` (a thin `readonly int|string $value` wrapper) exists in the
namespace but `Graph` itself doesn't use it internally — nodes are stored as
raw scalars. It's there for callers who want a typed node handle rather than
a bare value.

## Directed vs. undirected: `addEdge` never adds the reverse edge for you

This is the sharpest edge in the whole class. The `directed` flag is stored,
but `addEdge()` **doesn't consult it** — it always inserts exactly one entry,
`$adjacency[$source][] = new GraphEdge($source, $destination, ...)`.  For an
undirected graph, the *caller* is responsible for adding both directions
explicitly:

```php
$graph = new Graph(directed: false);
$graph->addNode('A');
$graph->addNode('B');
$graph->addEdge('A', 'B');
$graph->addEdge('B', 'A'); // <-- you must add this yourself
```

`removeEdge()`, by contrast, *does* consult `isDirected()` — removing `A→B`
on an undirected graph automatically also removes `B→A`:

```php
public function removeEdge(int|string $source, int|string $destination): void
{
    $this->CheckNodesExists($source, $destination);
    $this->adjacency[$source] = array_values(array_filter(
        $this->adjacency[$source] ?? [],
        fn(GraphEdge $edge) => $edge->getDestinationNode() !== $destination
    ));
    if (!$this->directed) {
        $this->adjacency[$destination] = array_values(array_filter(
            $this->adjacency[$destination] ?? [],
            fn(GraphEdge $edge) => $edge->getDestinationNode() !== $source
        ));
    }
}
```

So the class's symmetry handling is asymmetric between insertion and
removal — worth remembering when building undirected graphs: **always add
both directions yourself**, but you only ever need to call `removeEdge()`
once.

## Weighted-ness is inferred, then locked in

`weighted` isn't a constructor flag — the constructor only accepts
`directed`, and `$weighted` is always initialized to `false`:

```php
public function __construct(bool $directed = true) {
    $this->directed = $directed;
    $this->weighted = false;
    $this->nodes = [];
    $this->adjacency = [];
}
```

Instead, `isWeighted()`'s true value is decided implicitly by the **first
edge ever added** (whether via `addEdge()` directly or via
`buildFromAdjencyList()`, which just calls `addEdge()` per row):

```php
if ($this->getEdgeCount() === 0) {
    $this->weighted = $weight !== null;
} elseif ($this->weighted !== ($weight !== null)) {
    throw new InvalidArgumentException(
        'A graph cannot mix weighted and unweighted edges.'
    );
}
```

The first `addEdge()` call sets `$weighted` based on whether you passed a
`$weight`; every subsequent `addEdge()` must agree, or it throws. Since
`$weighted` always starts out as `false`, `isWeighted()` is safe to call at
any time — including on a brand-new graph with nodes but zero edges, which
correctly reports `false` rather than erroring. This also means
this matters for `DijkstraAlgorithm` (see [`12-dijkstra.md`](12-dijkstra.md))
even though it doesn't actually call `isWeighted()` itself — it validates
each edge's weight lazily as it relaxes across it, so an unweighted edge
still surfaces as a `RuntimeException` once the algorithm reaches it.

## Duplicate/missing-node/edge handling uses dedicated exceptions

- `addNode()` on a value that already exists throws
  `DuplicateNodeException::nodeDuplicate($value)`.
- `getEdge($source, $destination)` when no such edge exists throws
  `EdgeNotFoundException::edgeNotFound($source, $destination)`.
- `getNode($value)` when the value isn't a node throws
  `NotFoundException::nodeNotFound($value)` — despite the name, it just
  returns the value back if found (a validating passthrough, not a lookup
  into a richer object).
- Most other node/edge-existence checks throw plain
  `InvalidArgumentException` via the private `CheckNodesExists()` helper.

This mix (three custom exception classes plus base `InvalidArgumentException`
in different spots) is a known inconsistency rather than a deliberate
taxonomy — see `rating.md`/`PathToOnePointO.md` in the repo root for the
project's own note on it.

## Querying edges around a node

Four distinct queries, easy to mix up:

- **`getNeighbors($node)`** — outgoing edges stored directly under that
  node's adjacency-list key. O(1) lookup (`$this->adjacency[$value] ?? []`).
- **`getOutgoingEdges($node)`** — scans the *entire* adjacency list and
  filters for `edge->getSourceNode() === $node`. Semantically identical to
  `getNeighbors()` for how this class stores edges (since edges are only
  ever stored under their own source's key), but O(V+E) instead of O(1) —
  it re-derives the same answer the slow way.
- **`getIncomingEdges($node)`** — scans everything for
  `edge->getDestinationNode() === $node`. No reverse index is maintained, so
  this is always O(V+E); there's no faster path.
- **`getIncidentEdges($node)`** — union of both directions (source OR
  destination matches), also O(V+E).

## Building a graph from a plain array

`buildFromAdjencyList(array $data)` — `$data` maps each source node to a
list of edge rows, not bare destination values. Each row is
`['destination' => ..., 'weight' => ..., 'metadata' => ...]`, where only
`destination` is required (`weight` defaults to `null`, `metadata` to `[]`
— the same defaults `addEdge()` itself uses):

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

It calls `clear()` first, then adds every node it encounters (as either a
key or a `destination` value) before wiring the edges, so nodes that only
ever appear as a destination still get created. Each row is passed straight
through to `addEdge($source, $row['destination'], $row['weight'] ?? null,
$row['metadata'] ?? [])`, so the same weighted-locking rule from the section
above applies across the whole call — mixing rows with and without a
`weight` throws `InvalidArgumentException` from `addEdge()`, same as calling
it manually. This method also *does* end up creating a directed-looking
structure regardless of the `directed` flag, since it only calls `addEdge()`
once per row — same caveat as manual `addEdge()` above.

## The adjacency matrix

`getAdjencyMetrix()` builds a 1-indexed square array with node labels in row
0 / column 0 (`$data[0][j]` and `$data[$i][0]`), and `$data[$i][$j] = 1` if
an edge exists from the i-th to the j-th node in insertion order, `0`
otherwise. It's O(V²) (nested loop over `$keys × $keys`, with an O(E) inner
scan via `getNeighbors()` for each cell in the worst case). `printAdjacencyMatrix()`
renders it to stdout with `printf`-aligned columns; `display()` renders the
adjacency list itself as a tree (`●`/`├──►`/`└──►` box-drawing characters).

## Complexity summary

| Operation | Time |
|---|---|
| `addNode` | O(n) — `hasNode()` is a linear `in_array` scan |
| `addEdge` | O(n) — same `hasNode`/`CheckNodesExists` cost |
| `hasNode` / `getNode` | O(n) |
| `getNeighbors` | O(1) |
| `hasEdge` / `getEdge` | O(degree of source) |
| `getOutgoingEdges` / `getIncomingEdges` / `getIncidentEdges` | O(V+E) |
| `removeNode` | O(V+E) — filters every adjacency bucket |
| `removeEdge` | O(degree of source [+ destination if undirected]) |
| `getAdjencyMetrix` | O(V² · avg degree) |

`hasNode()`'s O(n) `in_array` scan (rather than an O(1) `isset()` on
`$adjacency`, which already has node values as keys) is a small optimization
opportunity worth knowing about if you're calling it in a hot loop — every
`addEdge`/`addNode`/`removeNode` call pays it at least once via
`CheckNodesExists()`.

See [`06-graph-traversal-bfs-dfs.md`](06-graph-traversal-bfs-dfs.md) and
[`07-graph-cycle-detection.md`](07-graph-cycle-detection.md) for the
algorithms built on top of `Graph`.
