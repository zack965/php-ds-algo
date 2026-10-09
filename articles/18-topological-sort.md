# Topological Sort

**Namespace:** `Zack\PhpDsAlgo\Algorithmes\Graph`
**Class:** `TopologicalSort`

## What it is

A **topological order** of a directed graph is a list of all its nodes in
which, for every edge `A → B`, `A` comes before `B`. It answers the question
"in what order can I do these things, given what depends on what?"

```php
use Zack\PhpDsAlgo\Algorithmes\Graph\TopologicalSort;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

$graph = new Graph(); // directed by default
foreach (['shirt', 'tie', 'jacket', 'belt', 'pants', 'shoes'] as $item) {
    $graph->addNode($item);
}
$graph->addEdge('shirt', 'tie');
$graph->addEdge('tie', 'jacket');
$graph->addEdge('shirt', 'belt');
$graph->addEdge('belt', 'jacket');
$graph->addEdge('pants', 'belt');
$graph->addEdge('pants', 'shoes');

TopologicalSort::run($graph);
// ['pants', 'shoes', 'shirt', 'tie', 'belt', 'jacket']
```

`run()` is a single static method. It takes any `IGraph` and returns a
`list<int|string>` of every node in the graph. It never changes the graph.

A graph usually has **many** valid orders. `['shirt', 'tie', 'pants', 'belt',
'shoes', 'jacket']` is just as correct as the one above. Rely on the
guarantee (every edge points forward), not on the exact sequence. The exact
sequence depends on node insertion order and edge insertion order.

## Preconditions

A topological order exists only for a **DAG**, a directed acyclic graph. `run()`
enforces both halves and throws `RuntimeException` otherwise:

| Problem | Message |
|---|---|
| The graph is undirected (`new Graph(false)`) | `The graph must be directed` |
| The graph has a cycle, including a self-loop | `The graph must be acyclic - (non cyclic): back edge X -> Y` |

The cycle message names the edge that closed the loop:

```php
$graph->addEdge('jacket', 'shirt'); // shirt → tie → jacket → shirt
TopologicalSort::run($graph);
// RuntimeException: The graph must be acyclic - (non cyclic): back edge jacket -> shirt
```

You don't need to call [`GraphDirectedCycleDetector`](07-graph-cycle-detection.md)
first. Cycle detection is built in, and it costs nothing extra because it
falls out of the same traversal.

## How it works

This is the **DFS finish-order** method, not Kahn's in-degree method.

1. Run a depth-first search from every node that hasn't been reached yet.
2. When a node is **finished**, meaning all of its descendants are already
   finished, append it to a result list.
3. Reverse the result list.

Why this works: a node is only finished after everything reachable from it
is finished. So in the finish list, every node comes *after* all of its
successors. Reversing flips that into "every node comes *before* its
successors," which is exactly a topological order.

### Three node states

Each node is in one of three states, kept in a `NodeState` enum:

| State | Meaning |
|---|---|
| `Unvisited` | Never reached. |
| `InProgress` | Entered, but its descendants aren't finished. The node is on the current DFS path. |
| `Done` | Fully finished and already in the result. |

The states are what make cycle detection free. When the search looks at a
neighbor, the state tells it what to do:

- `Unvisited`: explore it.
- `Done`: it was reached earlier by a different path, which is harmless (a
  cross edge, as in a diamond). Skip it.
- `InProgress`: the edge points back at a node on the current path. That is a
  back edge, so there's a cycle. Throw.

### Iterative, not recursive

The DFS uses an explicit stack instead of PHP recursion, so a long dependency
chain can't overflow the call stack. The test suite runs a 5,000-node chain.

Each stack entry is a node plus a `Task`:

- `Task::Enter`: start exploring this node.
- `Task::Finish`: all descendants are done, so record this node.

When a node is entered, the algorithm marks it `InProgress`, pushes its own
`Finish` task **first**, then pushes an `Enter` task for each unvisited
neighbor. Because the stack is last-in, first-out, the neighbors (and
everything beneath them) pop before the `Finish` task, which is what makes
the `Finish` fire only after all descendants are done.

Two details are easy to get wrong:

- **Mark at pop time, not push time.** A node can be pushed more than once
  before it's popped, for example two parents both pushing the same child. The
  algorithm marks a node `InProgress` only when it pops its `Enter`, and skips
  the entry if the node has already moved past `Unvisited`. Marking at push
  time would report false cycles or skip nodes.
- **`Finish` is how a node leaves `InProgress`.** Since the state changes to
  `Done` only when the `Finish` task pops, a neighbor seen in the
  `InProgress` state really is an ancestor on the current path.

### Trace

For the small chain `A → B → C`:

```
stack: [A:Enter]
pop A:Enter   → A InProgress; push A:Finish, B:Enter
pop B:Enter   → B InProgress; push B:Finish, C:Enter
pop C:Enter   → C InProgress; push C:Finish
pop C:Finish  → C Done; result [C]
pop B:Finish  → B Done; result [C, B]
pop A:Finish  → A Done; result [C, B, A]
reverse       → [A, B, C]
```

## Complexity

- **Time:** O(V + E). Each node is entered and finished once, and each edge
  is looked at once.
- **Space:** O(V) for the state map, the result list and the stack.

## Disconnected graphs and isolated nodes

`run()` starts a new search from every node still `Unvisited`, so components
that no single start node can reach are covered. Nodes with no edges at all
are included in the result, in no particular position relative to the other
components.

## When to use it

- **Build and dependency order:** compile targets, package installs, module
  loading.
- **Task scheduling:** "B can't start until A is done" rules.
- **Course prerequisites:** a valid study plan.
- **Spreadsheet-style recalculation:** evaluate each cell after the cells it
  reads.

## When to choose something else

- **You only need to know whether a cycle exists:** use
  [`GraphDirectedCycleDetector`](07-graph-cycle-detection.md); it returns a
  boolean instead of throwing.
- **Shortest or cheapest route:** use [Dijkstra](12-dijkstra.md). Topological
  sort ignores edge weights entirely.
- **Undirected graphs:** a topological order isn't defined for them, so
  `run()` throws.

## Testing

`tests/Unit/Algorithmes/TopologicalSortTest.php` has 17 tests (100% method and
line coverage) covering empty and single-node graphs, chains (including one
inserted in reverse order), a node stacked twice before it is popped, diamonds, disconnected components, integer and string nodes, a 5,000-node
chain, and every error path: undirected graphs, self-loops, two-node and
longer cycles, and a cycle in a separate component.

The tests assert the *property* (every edge goes from an earlier to a later
position) wherever more than one order is valid, and an exact order only where
the order is forced.
