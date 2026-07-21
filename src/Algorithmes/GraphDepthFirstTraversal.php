<?php

namespace Zack\PhpDsAlgo\Algorithmes;

use Zack\PhpDsAlgo\Contracts\IGraph;
use Zack\PhpDsAlgo\Exception\NotFoundException;

final class GraphDepthFirstTraversal
{
    public static function traverse(IGraph $graph, int|string $start): array
    {
        if (!$graph->hasNode($start)) {
            throw NotFoundException::nodeNotFound($start);
        }

        $visited = [];
        $stack = [$start];

        while (!empty($stack)) {
            // Get and remove the node from the top of the stack.
            $node = array_pop($stack);

            // Skip nodes that have already been visited.
            if (GeneralArrayAlgorithms::contains($visited, $node)) {
                continue;
            }

            // Mark the node as visited.
            $visited[] = $node;

            // Retrieve all neighbors.
            $neighbors = $graph->getNeighbors($node);

            // Push unvisited neighbors onto the stack.
            foreach ($neighbors as $neighbor) {
                if (!GeneralArrayAlgorithms::contains($visited, $neighbor->getDestinationNode())) {
                    $stack[] = $neighbor->getDestinationNode();
                }
            }
        }

        return $visited;
    }
}
