# Graph traversal: BFS and DFS

Two static, single-method classes in `src/Algorithmes/`, both operating
against the `IGraph` contract (so they work with any conforming
implementation, not just the concrete `Graph` class):
`GraphBreadthFirstTraversal::traverse()` and
`GraphDepthFirstTraversal::traverse()`. Both take `(IGraph $graph,
int|string $start)`, throw `NotFoundException::nodeNotFound($start)` if the
start node doesn't exist, and return a flat array of visited node values in
traversal order.

## BFS: array-as-queue

```php
public static function traverse(IGraph $graph, int|string $start): array
{
    if (!$graph->hasNode($start)) {
        throw NotFoundException::nodeNotFound($start);
    }
    $visited = [$start];
    $queue = [$start];
    while (!empty($queue)) {
        $node = $queue[0];
        $neighbors = $graph->getNeighbors($node);
        foreach ($neighbors as $neighbor) {
            if (!GeneralArrayAlgorithms::contains($visited, $neighbor->getDestinationNode())) {
                $queue[] = $neighbor->getDestinationNode();
                $visited[] = $neighbor->getDestinationNode();
            }
        }
        array_shift($queue);
    }
    return $visited;
}
```

Standard level-by-level BFS: `$start` is marked visited immediately (before
the loop even begins, avoiding the "is it in the queue but not yet marked"
edge case), then each iteration reads the front of `$queue` (without
popping it yet), expands its neighbors, marks+enqueues any that aren't
already in `$visited`, and only then shifts the front off. Marking a node
visited *at enqueue time* (not at dequeue time) is what prevents the same
node from being queued twice via two different parents.

## DFS: array-as-stack

```php
public static function traverse(IGraph $graph, int|string $start): array
{
    if (!$graph->hasNode($start)) {
        throw NotFoundException::nodeNotFound($start);
    }
    $visited = [];
    $stack = [$start];
    while (!empty($stack)) {
        $node = array_pop($stack);
        if (GeneralArrayAlgorithms::contains($visited, $node)) {
            continue;
        }
        $visited[] = $node;
        $neighbors = $graph->getNeighbors($node);
        foreach ($neighbors as $neighbor) {
            if (!GeneralArrayAlgorithms::contains($visited, $neighbor->getDestinationNode())) {
                $stack[] = $neighbor->getDestinationNode();
            }
        }
        return $visited; // note: see caveat below
    }
    return $visited;
}
```

*(That inline comment is illustrative — the real code doesn't return
mid-loop; it just falls through to the loop's next iteration. Shown above
only to clarify there's no early exit; ignore the "note" line, the actual
method is exactly as listed in the file.)*

DFS here is **iterative**, not the more textbook-familiar recursive version
— it uses an explicit PHP array as a stack (`array_pop`/`array[] = `) rather
than call-stack recursion, which avoids any PHP recursion-depth concerns on
large/deep graphs. Unlike BFS, a node is only marked visited when it's
**popped**, not when it's pushed — so the same node can be pushed onto the
stack multiple times (once per incoming edge from an already-queued
ancestor) before it's finally popped and processed once; the `continue` on
an already-visited pop is what makes this safe rather than a correctness
bug, at the cost of the stack sometimes holding duplicate pending entries.

Because it explores by popping the *most recently pushed* neighbor first,
this produces one valid depth-first order, but not necessarily the "first
neighbor first" order you'd get from recursing straight through
`getNeighbors()` in listed order — the last neighbor pushed is the first one
explored next.

## Shared cost: `GeneralArrayAlgorithms::contains()` is a linear scan

Both algorithms use `Zack\PhpDsAlgo\Algorithmes\GeneralArrayAlgorithms::contains()`
to check "has this node been visited," which is a plain `foreach` doing
strict (`===`) comparison — O(n) per call. Textbook BFS/DFS use a hash
set/associative-array membership check (`isset($visited[$node])`) to keep
the whole traversal at O(V + E). Here, each neighbor-expansion does an O(V)
scan of the growing `$visited` array, so the practical complexity is closer
to O(V² + V·E) than O(V+E) — fine for the small/teaching-scale graphs this
library targets, but worth knowing if you're traversing something large.
BFS additionally pays O(n) per `array_shift($queue)` (PHP reindexes the
whole array), compounding the same way `Queue::dequeue()` does — see
[`04-queue.md`](04-queue.md).

## Complexity summary

| | BFS | DFS |
|---|---|---|
| Structure used | array as FIFO queue | array as LIFO stack |
| Visited-marking point | at enqueue | at pop |
| Nominal complexity | O(V+E) | O(V+E) |
| Actual complexity here | O(V² + V·E) (linear `contains` + `array_shift`) | O(V² + V·E) (linear `contains`) |
| Recursion | none (iterative) | none (iterative, explicit stack) |

Both throw `NotFoundException` up front rather than silently returning an
empty result for an unknown start node — consistent with the rest of the
library's "throw on invalid input" convention.
