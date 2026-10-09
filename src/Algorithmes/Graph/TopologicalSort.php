<?php


namespace Zack\PhpDsAlgo\Algorithmes\Graph;

use RuntimeException;

use Zack\PhpDsAlgo\Contracts\IGraph;

enum NodeState: string
{
    case Unvisited  = 'unvisited';   // never reached
    case InProgress = 'in_progress'; // entered, descendants not finished (on current DFS path)
    case Done       = 'done';        // fully finished, already in result
}

enum Task: string
{
    case Enter  = 'enter';   // start exploring this node
    case Finish = 'finish';  // all descendants done, record this node
}
class TopologicalSort
{
    /**
     * @return list<int|string>
     */
    public static function run(IGraph $graph): array
    {
        if (!$graph->isDirected()) {
            throw new RuntimeException("The graph must be directed");
        }

        /** @var array<int|string, NodeState> $state */
        $state = [];
        $result = [];
        // $discovered = [];


        foreach ($graph->getNodes() as $node) {
            if (self::stateOf($state, $node) === NodeState::Unvisited) {
                self::exploreFrom($graph, $node, $state, $result);
            }
        }

        return array_reverse($result);
    }
    /**
     * @param array<int|string, NodeState> $state
     */
    private static function stateOf(array $state, int|string $node): NodeState
    {
        return $state[$node] ?? NodeState::Unvisited;
    }
    /**
     * @param array<int|string, NodeState> $state
     * @param list<int|string> $result
     */
    private static function exploreFrom(IGraph $graph, int|string $start, array &$state, array &$result): void
    {
        /** @var list<array{int|string, Task}> $stack */
        $stack = [[$start, Task::Enter]];
        while (!empty($stack)) {
            // Get and remove the node from the top of the stack.
            [$node, $task] = array_pop($stack);

            if ($task === Task::Finish) {
                $state[$node] = NodeState::Done;
                $result[] = $node;
                continue;
            }

            // Duplicate stack entry: node was already entered or finished.
            if (self::stateOf($state, $node) !== NodeState::Unvisited) {
                continue;
            }
            // Mark at pop time, not at push time.
            $state[$node] = NodeState::InProgress;

            // Pushed first, so it pops AFTER all neighbors and their subtrees.
            $stack[] = [$node, Task::Finish];



            // Push unvisited neighbors onto the stack.
            foreach ($graph->getNeighbors($node) as $neighbor) {
                $next = $neighbor->getDestinationNode();
                match (self::stateOf($state, $next)) {
                    NodeState::InProgress => throw new RuntimeException(
                        "The graph must be acyclic - (non cyclic): back edge {$node} -> {$next}"
                    ),
                    NodeState::Unvisited  => $stack[] = [$next, Task::Enter],
                    NodeState::Done       => null, // already finished, nothing to do
                };
            }
        }
    }
}
