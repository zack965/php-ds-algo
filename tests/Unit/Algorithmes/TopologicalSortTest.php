<?php

namespace Tests\Unit\Algorithmes;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zack\PhpDsAlgo\Algorithmes\Graph\TopologicalSort;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;

class TopologicalSortTest extends TestCase
{
    /**
     * @param list<array{int|string, int|string}> $edges
     */
    private function buildGraph(array $nodes, array $edges): Graph
    {
        $graph = new Graph();
        foreach ($nodes as $node) {
            $graph->addNode($node);
        }
        foreach ($edges as [$from, $to]) {
            $graph->addEdge($from, $to);
        }
        return $graph;
    }

    /**
     * Asserts every edge goes from an earlier to a later position.
     */
    private function assertValidOrder(array $order, array $edges): void
    {
        $position = array_flip($order);
        foreach ($edges as [$from, $to]) {
            $this->assertLessThan(
                $position[$to],
                $position[$from],
                "Edge {$from} -> {$to} violates the topological order"
            );
        }
    }

    public function testEmptyGraphReturnsEmptyArray(): void
    {
        $this->assertSame([], TopologicalSort::run(new Graph()));
    }

    public function testSingleNodeReturnsThatNode(): void
    {
        $graph = $this->buildGraph(['A'], []);

        $this->assertSame(['A'], TopologicalSort::run($graph));
    }

    public function testLinearChainReturnsExactOrder(): void
    {
        $edges = [['A', 'B'], ['B', 'C'], ['C', 'D']];
        $graph = $this->buildGraph(['A', 'B', 'C', 'D'], $edges);

        $this->assertSame(['A', 'B', 'C', 'D'], TopologicalSort::run($graph));
    }

    public function testChainInsertedInReverseStillOrdersCorrectly(): void
    {
        $edges = [['C', 'D'], ['B', 'C'], ['A', 'B']];
        $graph = $this->buildGraph(['D', 'C', 'B', 'A'], $edges);

        $this->assertSame(['A', 'B', 'C', 'D'], TopologicalSort::run($graph));
    }

    public function testDiamondDagRespectsAllEdges(): void
    {
        $edges = [['A', 'B'], ['A', 'C'], ['B', 'D'], ['C', 'D']];
        $graph = $this->buildGraph(['A', 'B', 'C', 'D'], $edges);

        $order = TopologicalSort::run($graph);

        $this->assertCount(4, $order);
        $this->assertSame('A', $order[0]);
        $this->assertSame('D', $order[3]);
        $this->assertValidOrder($order, $edges);
    }

    public function testDisconnectedNodesAreAllIncluded(): void
    {
        $edges = [['A', 'B'], ['C', 'D']];
        $graph = $this->buildGraph(['A', 'B', 'C', 'D', 'E'], $edges);

        $order = TopologicalSort::run($graph);

        $this->assertEqualsCanonicalizing(['A', 'B', 'C', 'D', 'E'], $order);
        $this->assertValidOrder($order, $edges);
    }

    public function testNodesWithoutEdgesAreAllReturned(): void
    {
        $graph = $this->buildGraph(['X', 'Y', 'Z'], []);

        $order = TopologicalSort::run($graph);

        $this->assertEqualsCanonicalizing(['X', 'Y', 'Z'], $order);
    }

    public function testIntegerNodes(): void
    {
        $edges = [[5, 2], [5, 0], [4, 0], [4, 1], [2, 3], [3, 1]];
        $graph = $this->buildGraph([0, 1, 2, 3, 4, 5], $edges);

        $order = TopologicalSort::run($graph);

        $this->assertCount(6, $order);
        $this->assertEqualsCanonicalizing([0, 1, 2, 3, 4, 5], $order);
        $this->assertValidOrder($order, $edges);
    }

    public function testEachNodeAppearsExactlyOnce(): void
    {
        $edges = [['A', 'B'], ['A', 'C'], ['B', 'D'], ['C', 'D'], ['D', 'E']];
        $graph = $this->buildGraph(['A', 'B', 'C', 'D', 'E'], $edges);

        $order = TopologicalSort::run($graph);

        $this->assertSame($order, array_values(array_unique($order)));
    }

    public function testDoesNotModifyGraph(): void
    {
        $edges = [['A', 'B'], ['B', 'C']];
        $graph = $this->buildGraph(['A', 'B', 'C'], $edges);

        TopologicalSort::run($graph);

        $this->assertSame(3, $graph->getNodesCount());
        $this->assertSame(2, $graph->getEdgeCount());
    }

    public function testLongChainDoesNotOverflow(): void
    {
        $n = 5000;
        $nodes = range(0, $n - 1);
        $edges = [];
        for ($i = 0; $i < $n - 1; $i++) {
            $edges[] = [$i, $i + 1];
        }
        $graph = $this->buildGraph($nodes, $edges);

        $this->assertSame($nodes, TopologicalSort::run($graph));
    }

    public function testUndirectedGraphThrows(): void
    {
        $graph = new Graph(false);
        $graph->addNode('A')->addNode('B');
        $graph->addEdge('A', 'B');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The graph must be directed');

        TopologicalSort::run($graph);
    }

    public function testSelfLoopThrows(): void
    {
        $graph = $this->buildGraph(['A'], [['A', 'A']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('acyclic');

        TopologicalSort::run($graph);
    }

    public function testTwoNodeCycleThrows(): void
    {
        $graph = $this->buildGraph(['A', 'B'], [['A', 'B'], ['B', 'A']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('acyclic');

        TopologicalSort::run($graph);
    }

    public function testLongerCycleThrows(): void
    {
        $graph = $this->buildGraph(
            ['A', 'B', 'C', 'D'],
            [['A', 'B'], ['B', 'C'], ['C', 'D'], ['D', 'B']]
        );

        $this->expectException(RuntimeException::class);

        TopologicalSort::run($graph);
    }

    public function testCycleInSeparateComponentThrows(): void
    {
        $graph = $this->buildGraph(
            ['A', 'B', 'C', 'D'],
            [['A', 'B'], ['C', 'D'], ['D', 'C']]
        );

        $this->expectException(RuntimeException::class);

        TopologicalSort::run($graph);
    }
}
