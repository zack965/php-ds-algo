# DijkstraAlgorithm: single-source shortest paths on top of `PriorityQueue`

`Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm\DijkstraAlgorithm` computes
shortest-path distances from one source node to every other node in a
weighted `Graph`, using the `PriorityQueue`/`MinHeap` pair documented in
[`13-heap.md`](13-heap.md) as its priority queue. This supersedes the earlier
scaffolding-only stub described in this article's previous revision (two
static methods, `computeToEveryNode()`/`computeBetweenTwoNodes()`, that
validated `isWeighted()` and then always returned `[]`) — the class has been
rewritten from scratch with a different shape entirely: it's stateful, lives
in its own `DijkstraAlgorithm/` subfolder alongside a small value-object
companion class, and doesn't call `isWeighted()` at all.

## Shape: an instance, not a static utility

Every other class under `src/Algorithmes/` (`ArraySortAlgorythmes`,
`GraphBreadthFirstTraversal`, `LevenshteinDistance`, ...) is a bag of static
methods with no state. `DijkstraAlgorithm` breaks that pattern — you
construct one, run the algorithm on it, then query the result off the same
instance:

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

$dijkstra->findShortestPath('D'); // ['D', 'B', 'C', 'A'] — target-to-source order
$dijkstra->display();             // pretty-prints every node's distance/visited/previous to stdout
```

That's why the constructor exists at all: it builds one
`PriorityQueue(PriorityQueueTypeEnum::Min)` up front, and `calculateDistances()`
reuses it rather than taking one as a parameter.

## `calculateDistances()`: the relaxation loop

```php
public function calculateDistances(IGraph $graph, int|string $sourceNode)
{
    if (!$graph->hasNode($sourceNode)) {
        throw new RuntimeException("No node with this value");
    }
    $this->distances = [];
    $nodes = $graph->getNodes();
    $this->initializeDistances($nodes, $sourceNode);
    $this->priorityQueue->insert($sourceNode, 0);

    while (!$this->priorityQueue->isEmpty()) {
        $node = $this->priorityQueue->extract();
        $value = $node->getValue();
        $entry = $this->distances[$value];
        if ($entry->isVisited()) {
            continue;
        }
        $entry->setIsVisited(true);
        $edges = $graph->getNeighbors($value);
        foreach ($edges as $edge) {
            $dest = $edge->getDestinationNode();
            $destEntry = $this->distances[$dest];
            if ($destEntry->isVisited()) {
                continue;
            }
            $weight = $edge->getWeight();
            if (!is_numeric($weight)) {
                throw new RuntimeException("the weight is not a numeric value");
            }
            $condidate = $entry->getShortestDistance() + $weight;
            if ($condidate < $destEntry->getShortestDistance()) {
                $destEntry->setShortestDistance($condidate);
                $destEntry->setPreviousNode($value);
                $this->priorityQueue->insert($dest, $condidate);
            }
        }
    }
}
```

Standard Dijkstra, with one implementation detail worth calling out:
`MinHeap`/`PriorityQueue` don't support a `decreaseKey()` operation, so this
can't do the textbook "find the existing queue entry for a node and lower
its priority in place." Instead it uses the common workaround for a
decrease-key-less heap — **lazy deletion**:

- Every time a shorter distance to `$dest` is found, a *new* entry for
  `$dest` is pushed onto the queue (`insert($dest, $condidate)`) rather than
  updating an old one. A node can end up with several stale entries sitting
  in the queue at different priorities.
- When an entry is popped (`extract()`), `isVisited()` is checked first
  (line just after `$entry = $this->distances[$value]`) — if that node was
  already finalized by an earlier, cheaper pop, the stale entry is simply
  skipped via `continue`. Because it's a `MinHeap`-backed queue, the
  *first* pop for any given node is always its cheapest, so this is safe:
  every later pop for the same node is guaranteed staler, never better.

There's a second, distinct staleness check a few lines later
(`if ($destEntry->isVisited()) continue;` inside the `foreach ($edges as
$edge)` loop) — this one skips even *attempting* to relax a neighbor that's
already been finalized. It's not required for correctness (the
`$condidate < $destEntry->getShortestDistance()` comparison right below
would already reject a worse candidate against a finalized, truly-shortest
distance), but it saves a comparison and an unnecessary `insert()` call.

Edge weights are validated lazily, one edge at a time, right when Dijkstra
actually needs to relax across it (`is_numeric($weight)` — `RuntimeException`
otherwise) — not eagerly via `$graph->isWeighted()` before the algorithm
starts. Practically: on a fully unweighted graph this still throws quickly
(the very first edge scanned has a `null` weight), but on a graph where only
some reachable edges are unweighted, whether it throws — and how much work
happens before it does — depends on traversal order, not on graph shape
alone.

## `DijkstraAlgorithmDistance`: the per-node working state

`initializeDistances()` seeds one `DijkstraAlgorithmDistance` per graph node
before the main loop starts — the source gets `shortestDistance: 0`,
everyone else `INF`:

```php
class DijkstraAlgorithmDistance
{
    public function __construct(
        private bool $isVisited = false,
        private mixed $value = null,
        private int|float $shortestDistance = INF,
        private mixed $previousNode = null,
    ) {}
    // isVisited()/setIsVisited(), getValue()/setValue(),
    // getShortestDistance()/setShortestDistance(),
    // getPreviousNode()/setPreviousNode()
}
```

It's a plain mutable value object — `calculateDistances()` reaches into
`$this->distances[$value]` and calls its setters directly as the algorithm
runs, rather than replacing entries wholesale. `$previousNode` is what makes
`findShortestPath()` possible afterward: it's the predecessor-pointer chain
Dijkstra builds as a side effect of relaxation.

## `findShortestPath()`: walk the predecessor chain backward

```php
public function findShortestPath(int|string $targetNode)
{
    if (!array_key_exists($targetNode, $this->distances)) {
        throw new RuntimeException("Target node does not exist");
    }
    $node = $this->distances[$targetNode];
    $nodes = [$node->getValue()];
    while (!is_null($node->getPreviousNode())) {
        $previousNode = $node->getPreviousNode();
        $nodes[] = $previousNode;
        $node = $this->distances[$previousNode];
    }
    return $nodes;
}
```

Must be called after `calculateDistances()` — it only ever reads
`$this->distances`, which is empty until a run has populated it. The
returned path is **target-to-source order**, not source-to-target — reverse
it yourself if you want it the other way round. An unreachable node (never
relaxed, `previousNode` stays `null`) returns a single-element array: just
itself.

## `display()`: pretty-printed dump, not part of the algorithm

```php
$dijkstra->display();
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//  Dijkstra Shortest Distances
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
//
// ● A      distance: 0        visited: yes previous: -
// ● C      distance: 1        visited: yes previous: A
// ...
```

Echoes the whole `$this->distances` table to stdout (`"No distances computed
yet."` if called before `calculateDistances()`), with `∞` rendered for any
node still at `INF`. Purely a debugging/demo aid — nothing else in the class
depends on it.

## Error handling

| Condition | Thrown |
|---|---|
| `calculateDistances($graph, $sourceNode)` where `$sourceNode` isn't in `$graph` | `RuntimeException("No node with this value")` |
| An edge relaxed during the run has a non-numeric weight (i.e. `null` — an unweighted edge) | `RuntimeException("the weight is not a numeric value")` |
| `findShortestPath($targetNode)` where `$targetNode` isn't a key in `$this->distances` (never computed, or wrong spelling/type) | `RuntimeException("Target node does not exist")` |

## Complexity

| Operation | Time | Notes |
|---|---|---|
| `calculateDistances()` | O(E log E) | every relaxation can push a new queue entry instead of decreasing one in place, so up to O(E) entries pass through the `MinHeap`, each an O(log E) `insert()`/`extract()` — equivalent to the textbook O(E log V) since E ≤ V² |
| `findShortestPath()` | O(path length) | one predecessor-pointer walk, no re-computation |
| `display()` | O(V) | one line per initialized node |

## Known gap: test coverage doesn't reach every branch yet

`tests/Unit/Algorithmes/DijkstraAlgorithm/DijkstraAlgorithmTest.php` covers
the shortest-distance computation, the cheaper-route-over-fewer-hops case,
an unreachable node, a missing source node, a single-node graph, and an
unweighted graph — but as of this writing `display()` is never called by any
test (0% line coverage), the `findShortestPath()` "target doesn't exist"
`RuntimeException` branch is untested, and the edge-loop's
already-visited-neighbor skip (`if ($destEntry->isVisited()) continue;`
inside `foreach ($edges as $edge)`, described above) never fires in any
existing test graph — all of them are pure DAGs with no edge pointing back
into an already-finalized node. Method coverage for the class currently sits
at 2/5 (`__construct` and the private `initializeDistances()`) because of
this, well short of the "100% method coverage on everything shipped" bar
`PathToOnePointO.md` sets for 1.0.

## Where this fits in the bigger picture

This is the library's first real shortest-path algorithm and the second
consumer of `PriorityQueue` (see [`13-heap.md`](13-heap.md)) after the heap
itself — exactly the gap `PathToOnePointO.md`'s M2 milestone called out:
BFS/DFS (see [`06-graph-traversal-bfs-dfs.md`](06-graph-traversal-bfs-dfs.md))
can only answer "fewest edges," not "lowest total weight." Topological sort,
Bellman-Ford, and Kruskal's/Prim's MST remain open on the same milestone.
