<?php

namespace Tests\Unit\Algorithmes\DijkstraAlgorithm;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm\DijkstraAlgorithm;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

class DijkstraAlgorithmTest extends TestCase
{
    /**
     * A -> C -> B -> D -> E, with A -> B and C -> D as costlier alternatives.
     *
     *   A --4--> B
     *   A --1--> C
     *   C --2--> B
     *   B --1--> D
     *   C --5--> D
     *   D --3--> E
     *
     * Shortest distances from A: A=0, C=1, B=3 (A-C-B), D=4 (A-C-B-D), E=7.
     */
    private function buildWeightedGraph(): Graph
    {
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C')->addNode('D')->addNode('E');
        $graph->addEdge('A', 'B', 4);
        $graph->addEdge('A', 'C', 1);
        $graph->addEdge('C', 'B', 2);
        $graph->addEdge('B', 'D', 1);
        $graph->addEdge('C', 'D', 5);
        $graph->addEdge('D', 'E', 3);

        return $graph;
    }

    public function testCalculateDistancesFindsShortestDistanceToEveryNode(): void
    {
        $graph = $this->buildWeightedGraph();

        $dijkstra = new DijkstraAlgorithm();
        $dijkstra->calculateDistances($graph, 'A');

        $this->assertSame(['D', 'B', 'C', 'A'], $dijkstra->findShortestPath('D'));
        $this->assertSame(['E', 'D', 'B', 'C', 'A'], $dijkstra->findShortestPath('E'));
        $this->assertSame(['A'], $dijkstra->findShortestPath('A'));
    }

    public function testFindShortestPathPrefersTheCheaperRouteOverFewerHops(): void
    {
        $graph = $this->buildWeightedGraph();

        $dijkstra = new DijkstraAlgorithm();
        $dijkstra->calculateDistances($graph, 'A');

        // A -> B directly costs 4, but A -> C -> B costs only 3.
        $this->assertSame(['B', 'C', 'A'], $dijkstra->findShortestPath('B'));
    }

    public function testUnreachableNodeHasNoPreviousNodeAndPathIsJustItself(): void
    {
        $graph = $this->buildWeightedGraph();
        $graph->addNode('F'); // no edges in or out

        $dijkstra = new DijkstraAlgorithm();
        $dijkstra->calculateDistances($graph, 'A');

        $this->assertSame(['F'], $dijkstra->findShortestPath('F'));
    }

    public function testCalculateDistancesThrowsWhenSourceNodeDoesNotExist(): void
    {
        $graph = $this->buildWeightedGraph();

        $dijkstra = new DijkstraAlgorithm();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No node with this value');

        $dijkstra->calculateDistances($graph, 'Z');
    }

    public function testCalculateDistancesThrowsOnSingleNodeGraphWithoutTraversingAnyEdge(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $dijkstra = new DijkstraAlgorithm();
        $dijkstra->calculateDistances($graph, 'A');

        $this->assertSame(['A'], $dijkstra->findShortestPath('A'));
    }

    public function testCalculateDistancesThrowsWhenGraphIsUnweighted(): void
    {
        // addEdge() without a weight leaves it null, which Dijkstra rejects
        // as soon as it inspects a neighbouring edge.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B');
        $graph->addEdge('A', 'B');

        $dijkstra = new DijkstraAlgorithm();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('the weight is not a numeric value');

        $dijkstra->calculateDistances($graph, 'A');
    }

    public function testCalculateDistancesIgnoresAStaleQueueEntryForAnAlreadyVisitedNode(): void
    {
        // C is reachable via two routes (A->C weight 1, A->B->C weight 1+1=2),
        // so the priority queue holds a stale, costlier entry for C that must
        // be skipped once C has already been visited via the cheaper route.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C');
        $graph->addEdge('A', 'B', 1);
        $graph->addEdge('A', 'C', 1);
        $graph->addEdge('B', 'C', 1);

        $dijkstra = new DijkstraAlgorithm();
        $dijkstra->calculateDistances($graph, 'A');

        $this->assertSame(['C', 'A'], $dijkstra->findShortestPath('C'));
    }
}
