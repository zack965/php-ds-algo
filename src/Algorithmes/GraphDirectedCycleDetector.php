<?php

namespace Zack\PhpDsAlgo\Algorithmes;

use Zack\PhpDsAlgo\Contracts\IGraph;
use Zack\PhpDsAlgo\Exception\NotFoundException;

class GraphDirectedCycleDetector
{
    public static function detect(IGraph $graph): bool
    {
        $visited = [];
        $recursionStack = [];
        $adjency = $graph->getAdjency();

        foreach ($adjency as $node => $edges) {
            if (!in_array($node, $visited) && self::traverse($graph, $node, $visited, $recursionStack)) {
                // start a DFS from it.
                return true;
            }
        }

        return false;
    }

    private static function traverse(IGraph $graph, int|string $start, array &$visited, array &$recursionStack): bool
    {
        if (!$graph->hasNode($start)) {
            throw NotFoundException::nodeNotFound($start);
        }

        // Mark the node as visited and add it to the current path.
        $visited[] = $start;
        $recursionStack[] = $start;

        $neighbors = $graph->getNeighbors($start);

        foreach ($neighbors as $neighbor) {
            $destination = $neighbor->getDestinationNode();

            // A neighbor already on the current recursion path means a back edge -> cycle.
            if (GeneralArrayAlgorithms::contains($recursionStack, $destination)) {
                return true;
            }

            // Otherwise, recurse into unvisited neighbors.
            if (!GeneralArrayAlgorithms::contains($visited, $destination) && self::traverse($graph, $destination, $visited, $recursionStack)) {
                return true;
            }
        }

        // Done exploring this node's subtree; remove it from the current path.
        array_pop($recursionStack);

        return false;
    }
}
