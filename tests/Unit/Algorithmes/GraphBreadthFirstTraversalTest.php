<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\GraphBreadthFirstTraversal;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;
use Zack\PhpDsAlgo\Exception\NotFoundException;

class GraphBreadthFirstTraversalTest extends TestCase
{
    private function buildDiamondGraph(): Graph
    {
        // A -> B, A -> C, B -> D, C -> D
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C')->addNode('D');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');
        $graph->addEdge('B', 'D');
        $graph->addEdge('C', 'D');

        return $graph;
    }

    public function testTraverseVisitsNodesInBreadthFirstOrder(): void
    {
        $graph = $this->buildDiamondGraph();

        $result = GraphBreadthFirstTraversal::traverse($graph, 'A');

        $this->assertSame(['A', 'B', 'C', 'D'], $result);
    }

    public function testTraverseOnSingleNodeGraphReturnsOnlyThatNode(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $result = GraphBreadthFirstTraversal::traverse($graph, 'A');

        $this->assertSame(['A'], $result);
    }

    public function testTraverseVisitsEachNodeOnlyOnce(): void
    {
        $graph = $this->buildDiamondGraph();

        $result = GraphBreadthFirstTraversal::traverse($graph, 'A');

        $this->assertCount(4, $result);
        $this->assertSame(array_unique($result), $result);
    }

    public function testTraverseThrowsWhenStartNodeMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->expectException(NotFoundException::class);

        GraphBreadthFirstTraversal::traverse($graph, 'Z');
    }
}
