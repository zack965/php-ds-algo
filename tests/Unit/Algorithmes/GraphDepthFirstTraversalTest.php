<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\GraphDepthFirstTraversal;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;
use Zack\PhpDsAlgo\Exception\NotFoundException;

class GraphDepthFirstTraversalTest extends TestCase
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

    public function testTraverseVisitsNodesInDepthFirstOrder(): void
    {
        $graph = $this->buildDiamondGraph();

        // Stack-based (LIFO) DFS: A's neighbors [B, C] are pushed in that
        // order, so C is popped and explored before B.
        $result = GraphDepthFirstTraversal::traverse($graph, 'A');

        $this->assertSame(['A', 'C', 'D', 'B'], $result);
    }

    public function testTraverseOnSingleNodeGraphReturnsOnlyThatNode(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $result = GraphDepthFirstTraversal::traverse($graph, 'A');

        $this->assertSame(['A'], $result);
    }

    public function testTraverseVisitsEachNodeOnlyOnce(): void
    {
        $graph = $this->buildDiamondGraph();

        $result = GraphDepthFirstTraversal::traverse($graph, 'A');

        $this->assertCount(4, $result);
        $this->assertSame(array_unique($result), $result);
    }

    public function testTraverseThrowsWhenStartNodeMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->expectException(NotFoundException::class);

        GraphDepthFirstTraversal::traverse($graph, 'Z');
    }

    public function testTraverseSkipsANodePushedTwiceBeforeItsFirstVisit(): void
    {
        // Graph::addEdge() allows duplicate edges, so A has two edges to X.
        // Both get pushed onto the stack in the same neighbor loop (since
        // visited-tracking only happens on pop, not push), so the second X
        // is already visited by the time it's popped — exercising the
        // "already visited, skip" continue branch.
        $graph = new Graph();
        $graph->addNode('A')->addNode('X');
        $graph->addEdge('A', 'X');
        $graph->addEdge('A', 'X');

        $result = GraphDepthFirstTraversal::traverse($graph, 'A');

        $this->assertSame(['A', 'X'], $result);
    }
}
