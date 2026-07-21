<?php


namespace Zack\PhpDsAlgo\Algorithmes;

use Zack\PhpDsAlgo\Contracts\IGraph;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;
use Zack\PhpDsAlgo\Exception\NotFoundException;

final class GraphBreadthFirstTraversal
{
    public static function traverse(IGraph $graph, int|string $start): array
    {
        if (!$graph->hasNode($start)) {
            throw NotFoundException::nodeNotFound($start);
        }

        $visited = [$start];
        $queue = [$start];
        while (!empty($queue)) {
            // get node that will  be processed
            $node = $queue[0];
            //get neighbirs
            $neighbors = $graph->getNeighbors($node);
            //add it's neighbors to the queue
            //add it's neighbors to the visited
            foreach ($neighbors as $neighbor) {
                if (!GeneralArrayAlgorithms::contains($visited, $neighbor->getDestinationNode())) {

                    $queue[] = $neighbor->getDestinationNode();
                    $visited[] = $neighbor->getDestinationNode();
                }
            }
            // remove it from queue
            array_shift($queue);
        }
        return $visited;
    }
}
