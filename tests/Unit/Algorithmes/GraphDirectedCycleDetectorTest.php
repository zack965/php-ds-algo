<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\GraphDirectedCycleDetector;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

class GraphDirectedCycleDetectorTest extends TestCase
{
    public function testDetectReturnsFalseForEmptyGraph(): void
    {
        $graph = new Graph();

        $this->assertFalse(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsFalseForSingleNodeWithoutEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->assertFalse(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsFalseForDiamondShapedDag(): void
    {
        // A -> B, A -> C, B -> D, C -> D.
        // D is reached twice (via B and via C) but that's a cross edge, not a
        // back edge, since D is fully visited (popped from the recursion
        // stack) by the time the second path reaches it.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C')->addNode('D');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');
        $graph->addEdge('B', 'D');
        $graph->addEdge('C', 'D');

        $this->assertFalse(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsTrueForDirectTwoNodeCycle(): void
    {
        // A -> B -> A.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'A');

        $this->assertTrue(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsTrueForSelfLoop(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addEdge('A', 'A');

        $this->assertTrue(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsTrueForLongerCycle(): void
    {
        // A -> B -> C -> A.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'C');
        $graph->addEdge('C', 'A');

        $this->assertTrue(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsFalseForDisconnectedAcyclicComponents(): void
    {
        // A -> B and, separately, C -> D. Neither component has a cycle.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C')->addNode('D');
        $graph->addEdge('A', 'B');
        $graph->addEdge('C', 'D');

        $this->assertFalse(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsTrueWhenCycleIsInASecondComponent(): void
    {
        // A -> B is acyclic and visited first, but C -> D -> C forms a cycle
        // in a separate component. Exercises the outer loop in detect()
        // continuing to unvisited nodes after an acyclic component.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C')->addNode('D');
        $graph->addEdge('A', 'B');
        $graph->addEdge('C', 'D');
        $graph->addEdge('D', 'C');

        $this->assertTrue(GraphDirectedCycleDetector::detect($graph));
    }

    public function testDetectReturnsFalseForLinearChain(): void
    {
        // A -> B -> C -> D.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C')->addNode('D');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'C');
        $graph->addEdge('C', 'D');

        $this->assertFalse(GraphDirectedCycleDetector::detect($graph));
    }
}
