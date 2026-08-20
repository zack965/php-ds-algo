<?php

namespace Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm;

use RuntimeException;
use Zack\PhpDsAlgo\Contracts\IGraph;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueue;
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueueNode;
use Zack\PhpDsAlgo\enums\PriorityQueueTypeEnum;

class DijkstraAlgorithm
{
    private PriorityQueue $priorityQueue;
    public function __construct()
    {
        $this->priorityQueue = new PriorityQueue(PriorityQueueTypeEnum::Min);
    }
    /**
     * @var list<DijkstraAlgorithmDistance>
     */
    private array $distances = [];


    private function initializeDistances(array $nodes, mixed $source): void
    {
        foreach ($nodes as $node) {
            $distanceObj = new DijkstraAlgorithmDistance(
                isVisited: false,
                value: $node,
                shortestDistance: ($node === $source) ? 0 : INF,
                previousNode: null
            );
            $this->distances[(string)$node] = $distanceObj;
        }
    }

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
            /** @var PriorityQueueNode $node */
            $node = $this->priorityQueue->extract();
            $value = $node->getValue();
            /** @var DijkstraAlgorithmDistance $node */
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
    public function display(): void
    {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo " Dijkstra Shortest Distances\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        if ($this->distances === []) {
            echo "No distances computed yet." . PHP_EOL;
            return;
        }

        foreach ($this->distances as $entry) {
            $value = $entry->getValue();
            $distance = $entry->getShortestDistance();
            $distanceLabel = $distance === INF ? "∞" : (string)$distance;
            $visitedLabel = $entry->isVisited() ? "yes" : "no";
            $previous = $entry->getPreviousNode();
            $previousLabel = $previous === null ? "-" : (string)$previous;

            printf(
                "● %-6s distance: %-8s visited: %-4s previous: %s\n",
                (string)$value,
                $distanceLabel,
                $visitedLabel,
                $previousLabel
            );
        }

        echo PHP_EOL;
    }
    public function findShortestPath(int|string $targetNode)
    {
        if (!array_key_exists($targetNode, $this->distances)) {
            throw new RuntimeException("Target node does not exist");
        }
        /** @var DijkstraAlgorithmDistance $node */
        $node = $this->distances[$targetNode];
        $nodes = [$node->getValue()];
        while (!is_null($node->getPreviousNode())) {
            $previousNode = $node->getPreviousNode();
            $nodes[] = $previousNode;
            //getValue()
            $node = $this->distances[$previousNode];
        }
        return $nodes;
    }
}
