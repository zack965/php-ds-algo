<?php


namespace Zack\PhpDsAlgo\Algorithmes;

use RuntimeException;
use Zack\PhpDsAlgo\Contracts\IGraph;
use Zack\PhpDsAlgo\Helpers\Algorythmes\DijkstraAlgorithmTable;

class DijkstraAlgorithm
{
    /*   private DijkstraAlgorithmTable $table;
    private function __construct(IGraph $iGraph)
    {
        $this->table = new DijkstraAlgorithmTable();

       
    } */
    public static function ShortestPath(IGraph $iGraph, int|string $sourceNode): array
    {
        if (!$iGraph->isWeighted()) {
            throw new RuntimeException(
                'Dijkstra\'s algorithm requires a weighted graph.'
            );
        }
        if (!$iGraph->hasNode($sourceNode)) {
            throw new RuntimeException(
                'no node with this value exists'
            );
        }
        $algorithm = new self($iGraph);

        $distanceArray =  self::BuildDistanceTable($iGraph, $sourceNode);
        return $distanceArray;
    }
    private static function BuildDistanceTable(IGraph $iGraph, int|string $sourceNode): array
    {
        $table = new DijkstraAlgorithmTable();
        foreach ($iGraph->getAdjency() as $node => $edges) {
            $table->addNode($node);
        }
        $events = [];
        $table->setDistance($sourceNode, 0);
        $events[] = sprintf(
            'Setting distance from sourceNode: %s, Distance: 0',
            $sourceNode
        );
        $visited = [$sourceNode];
        $queue = [$sourceNode];
        while (!empty($queue)) {
            // get node that will  be processed
            $node = $queue[0];

            $events[] = sprintf(
                'Processing node: %s',
                $node
            );
            //get neighbirs
            $neighbors = $iGraph->getNeighbors($node);
            //add it's neighbors to the queue
            //add it's neighbors to the visited

            foreach ($neighbors as $neighbor) {
                $destination = $neighbor->getDestinationNode();

                $weight = $neighbor->getWeight();

                if (!GeneralArrayAlgorithms::contains($visited, $destination)) {
                    $events[] = "Checking neighbor: {$destination} from {$node} with weight: {$weight}";
                    $queue[] = $destination;
                    $visited[] = $destination;
                    $table->setDistance($destination, $weight);
                    $events[] = "Setting distance of {$destination} to {$weight}";

                    // $table->setPreviousNode($node, $neighbor->getDestinationNode());
                }
            }
            $table->setVisited($node, true);


            $events[] = sprintf(
                'Marking node %s as visited',
                $node
            );

            $events[] = "-------------------------------------------";
            // remove it from queue
            array_shift($queue);
        }
        print_r($table);
        echo "Events : " . PHP_EOL;
        print_r($events);
        echo "Events : " . PHP_EOL;

        return $visited;
    }
}
