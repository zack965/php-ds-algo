<?php

namespace Tests\Unit\DataStructure\Graph;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Graph\Graph;
use Zack\PhpDsAlgo\DataStructure\Graph\GraphEdge;
use Zack\PhpDsAlgo\Exception\DuplicateNodeException;
use Zack\PhpDsAlgo\Exception\EdgeNotFoundException;
use Zack\PhpDsAlgo\Exception\NotFoundException;

class GraphTest extends TestCase
{
    // --- Construction ---

    public function testDefaultConstructorIsDirectedAndUnweighted(): void
    {
        $graph = new Graph();

        $this->assertTrue($graph->isDirected());
        $this->assertFalse($graph->isWeighted());
    }
    public function testConstructorHonoursDirectedAndWeightedFlags(): void
    {
        $graph = new Graph(directed: false, weighted: true);

        $this->assertFalse($graph->isDirected());
        $this->assertTrue($graph->isWeighted());
    }
    public function testNewGraphIsEmpty(): void
    {
        $graph = new Graph();

        $this->assertTrue($graph->isEmpty());
        $this->assertSame(0, $graph->getNodesCount());
        $this->assertSame([], $graph->getNodes());
    }

    // --- Nodes ---

    public function testAddNodeAddsToNodesAndAdjacency(): void
    {
        $graph = new Graph();

        $result = $graph->addNode('A');

        $this->assertSame($graph, $result);
        $this->assertTrue($graph->hasNode('A'));
        $this->assertSame(1, $graph->getNodesCount());
        $this->assertFalse($graph->isEmpty());
        $this->assertSame([], $graph->getNeighbors('A'));
    }
    public function testAddNodeThrowsOnDuplicate(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->expectException(DuplicateNodeException::class);

        $graph->addNode('A');
    }


    public function testHasNodeReturnsFalseForMissingNode(): void
    {
        $graph = new Graph();

        $this->assertFalse($graph->hasNode('A'));
    }

    public function testGetNodeReturnsNodeValue(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->assertSame('A', $graph->getNode('A'));
    }



    public function testGetNodeThrowsWhenNodeMissing(): void
    {
        $graph = new Graph();

        $this->expectException(NotFoundException::class);

        $graph->getNode('missing');
    }

    public function testGetNodesReturnsAllAddedNodes(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');

        $this->assertSame(['A', 'B'], $graph->getNodes());
    }

    public function testRemoveNodeRemovesNodeAndOutgoingEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        $result = $graph->removeNode('A');

        $this->assertSame($graph, $result);
        $this->assertFalse($graph->hasNode('A'));
        $this->assertSame(1, $graph->getNodesCount());
    }

    public function testRemoveNodeRemovesIncomingEdgesToRemovedNode(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('C', 'B');

        $graph->removeNode('B');

        $this->assertSame([], $graph->getNeighbors('A'));
        $this->assertSame([], $graph->getNeighbors('C'));
    }



    public function testRemoveNodeThrowsWhenNodeMissing(): void
    {
        $graph = new Graph();

        $this->expectException(InvalidArgumentException::class);

        $graph->removeNode('missing');
    }

    // --- Edges ---

    public function testAddEdgeCreatesEdgeInSourceAdjacency(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');

        $edge = $graph->addEdge('A', 'B');

        $this->assertInstanceOf(GraphEdge::class, $edge);
        $this->assertSame('A', $edge->getSourceNode());
        $this->assertSame('B', $edge->getDestinationNode());
        $this->assertTrue($graph->hasEdge('A', 'B'));
    }

    public function testAddEdgeStoresWeightAndMetadata(): void
    {
        $graph = new Graph(weighted: true);
        $graph->addNode('A');
        $graph->addNode('B');

        $edge = $graph->addEdge('A', 'B', 4.5, ['label' => 'road']);

        $this->assertSame(4.5, $edge->getWeight());
        $this->assertSame(['label' => 'road'], $edge->getMetadata());
    }


    public function testAddEdgeThrowsWhenSourceNodeMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('B');

        $this->expectException(InvalidArgumentException::class);

        $graph->addEdge('A', 'B');
    }

    public function testAddEdgeThrowsWhenDestinationNodeMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->expectException(InvalidArgumentException::class);

        $graph->addEdge('A', 'B');
    }

    public function testHasEdgeReturnsFalseWhenNoEdgeExists(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');

        $this->assertFalse($graph->hasEdge('A', 'B'));
    }


    public function testHasEdgeThrowsWhenSourceMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('B');

        $this->expectException(InvalidArgumentException::class);

        $graph->hasEdge('A', 'B');
    }

    public function testHasEdgeThrowsWhenDestinationMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->expectException(InvalidArgumentException::class);

        $graph->hasEdge('A', 'B');
    }

    public function testGetEdgeReturnsMatchingEdge(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B', 2);

        $edge = $graph->getEdge('A', 'B');

        $this->assertSame(2, $edge->getWeight());
    }

    public function testGetEdgeThrowsWhenEdgeMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');

        $this->expectException(EdgeNotFoundException::class);

        $graph->getEdge('A', 'B');
    }

    public function testGetEdgeThrowsWhenNodeMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->expectException(InvalidArgumentException::class);

        $graph->getEdge('A', 'missing');
    }


    public function testRemoveEdgeRemovesOnlySourceToDestinationOnDirectedGraph(): void
    {
        $graph = new Graph(directed: true);
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'A');

        $graph->removeEdge('A', 'B');

        $this->assertFalse($graph->hasEdge('A', 'B'));
        $this->assertTrue($graph->hasEdge('B', 'A'));
    }

    public function testRemoveEdgeRemovesBothDirectionsOnUndirectedGraph(): void
    {
        $graph = new Graph(directed: false);
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'A');

        $graph->removeEdge('A', 'B');

        $this->assertFalse($graph->hasEdge('A', 'B'));
        $this->assertFalse($graph->hasEdge('B', 'A'));
    }

    public function testRemoveEdgeThrowsWhenNodeMissing(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->expectException(InvalidArgumentException::class);

        $graph->removeEdge('A', 'missing');
    }



    public function testGetNeighborsReturnsOutgoingEdgesOfNode(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');

        $neighbors = $graph->getNeighbors('A');

        $this->assertCount(2, $neighbors);
        $this->assertContainsOnlyInstancesOf(GraphEdge::class, $neighbors);
    }

    public function testGetNeighborsReturnsEmptyArrayForNodeWithoutEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $this->assertSame([], $graph->getNeighbors('A'));
    }

    public function testGetOutgoingEdgesReturnsEdgesLeavingNode(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');
        $graph->addEdge('B', 'C');

        $edges = $graph->getOutgoingEdges('A');

        $this->assertCount(2, $edges);
        foreach ($edges as $edge) {
            $this->assertSame('A', $edge->getSourceNode());
        }
    }

    public function testGetIncomingEdgesReturnsEdgesEnteringNode(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'C');
        $graph->addEdge('B', 'C');

        $edges = $graph->getIncomingEdges('C');

        $this->assertCount(2, $edges);
        foreach ($edges as $edge) {
            $this->assertSame('C', $edge->getDestinationNode());
        }
    }

    public function testGetIncidentEdgesReturnsEdgesTouchingNode(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'C');
        $graph->addEdge('C', 'A');

        $edges = $graph->getIncidentEdges('B');

        $this->assertCount(2, $edges);
    }

    public function testGetEdgesReturnsAllEdgesAcrossGraph(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'C');

        $this->assertCount(2, $graph->getEdges());
    }

    public function testGetEdgeCountMatchesNumberOfEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');

        $this->assertSame(2, $graph->getEdgeCount());
    }

    public function testGetAdjencyReturnsRawAdjacencyList(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        $adjacency = $graph->getAdjency();

        $this->assertArrayHasKey('A', $adjacency);
        $this->assertArrayHasKey('B', $adjacency);
        $this->assertCount(1, $adjacency['A']);
        $this->assertSame([], $adjacency['B']);
    }

    // --- Iteration ---

    public function testGetIteratorYieldsAdjacencyEntries(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        $entries = [];
        foreach ($graph->getIterator() as $node => $edges) {
            $entries[$node] = $edges;
        }

        $this->assertArrayHasKey('A', $entries);
        $this->assertArrayHasKey('B', $entries);
        $this->assertCount(1, $entries['A']);
    }

    // --- Clearing ---

    public function testClearNodesEmptiesNodesButKeepsAdjacency(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        $result = $graph->clearNodes();

        $this->assertSame($graph, $result);
        $this->assertSame([], $graph->getNodes());
        $this->assertTrue($graph->isEmpty());
        $this->assertCount(1, $graph->getEdges());
    }

    public function testClearEdgesEmptiesEdgesButKeepsNodes(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        $result = $graph->clearEdges();

        $this->assertSame($graph, $result);
        $this->assertSame([], $graph->getEdges());
        $this->assertSame(0, $graph->getEdgeCount());
        $this->assertSame(['A', 'B'], $graph->getNodes());
    }

    public function testClearEmptiesBothNodesAndAdjacency(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        $result = $graph->clear();

        $this->assertSame($graph, $result);
        $this->assertTrue($graph->isEmpty());
        $this->assertSame([], $graph->getEdges());
        $this->assertSame([], $graph->getAdjency());
    }

    // --- Display ---

    public function testDisplayPrintsGraphSummaryForNonEmptyGraph(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        ob_start();
        $graph->display();
        $output = ob_get_clean();

        $this->assertStringContainsString('Graph', $output);
        $this->assertStringContainsString('Nodes : 2', $output);
        $this->assertStringContainsString('Edges : 1', $output);
        $this->assertStringContainsString('A', $output);
        $this->assertStringContainsString('B', $output);
    }

    public function testDisplayPrintsEmptyBranchForNodeWithoutEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        ob_start();
        $graph->display();
        $output = ob_get_clean();

        $this->assertStringContainsString('Nodes : 1', $output);
        $this->assertStringContainsString('Edges : 0', $output);
    }

    public function testDisplayPrintsBothBranchCharactersForANodeWithMultipleEdges(): void
    {
        // A single edge always lands on the "last" branch; a node needs 2+
        // outgoing edges to exercise the non-last "├──►" branch too.
        $graph = new Graph();
        $graph->addNode('A')->addNode('B')->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');

        ob_start();
        $graph->display();
        $output = ob_get_clean();

        $this->assertStringContainsString('├──►', $output);
        $this->assertStringContainsString('└──►', $output);
    }

    // --- buildFromAdjencyList ---

    public function testBuildFromAdjencyListCreatesNodesAndEdges(): void
    {
        $graph = new Graph();

        $graph->buildFromAdjencyList([
            'A' => ['B', 'C'],
            'B' => ['C'],
        ]);

        $this->assertSame(['A', 'B', 'C'], $graph->getNodes());
        $this->assertTrue($graph->hasEdge('A', 'B'));
        $this->assertTrue($graph->hasEdge('A', 'C'));
        $this->assertTrue($graph->hasEdge('B', 'C'));
        $this->assertSame(3, $graph->getEdgeCount());
    }

    public function testBuildFromAdjencyListAddsNodesThatOnlyAppearAsDestinations(): void
    {
        $graph = new Graph();

        $graph->buildFromAdjencyList(['A' => ['B']]);

        $this->assertTrue($graph->hasNode('B'));
    }

    public function testBuildFromAdjencyListClearsExistingGraphState(): void
    {
        $graph = new Graph();
        $graph->addNode('Z');

        $graph->buildFromAdjencyList(['A' => ['B']]);

        $this->assertFalse($graph->hasNode('Z'));
        $this->assertTrue($graph->hasNode('A'));
        $this->assertTrue($graph->hasNode('B'));
    }

    public function testBuildFromAdjencyListWithEmptyDataResultsInEmptyGraph(): void
    {
        $graph = new Graph();
        $graph->addNode('Z');

        $graph->buildFromAdjencyList([]);

        $this->assertTrue($graph->isEmpty());
    }

    // --- getAdjencyMetrix ---

    public function testGetAdjencyMetrixReturnsEmptyArrayForEmptyGraph(): void
    {
        $graph = new Graph();

        $this->assertSame([], $graph->getAdjencyMetrix());
    }

    public function testGetAdjencyMetrixForSingleNodeWithoutEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        $matrix = $graph->getAdjencyMetrix();

        $this->assertSame([
            0 => [1 => 'A'],
            1 => [0 => 'A', 1 => 0],
        ], $matrix);
    }

    public function testGetAdjencyMetrixBuildsHeaderRowAndColumnFromNodes(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');

        $matrix = $graph->getAdjencyMetrix();

        $this->assertSame([1 => 'A', 2 => 'B', 3 => 'C'], $matrix[0]);
        $this->assertSame('A', $matrix[1][0]);
        $this->assertSame('B', $matrix[2][0]);
        $this->assertSame('C', $matrix[3][0]);
    }

    public function testGetAdjencyMetrixMarksExistingEdgesWithOneAndOthersWithZero(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');

        $matrix = $graph->getAdjencyMetrix();

        $this->assertSame([
            0 => [1 => 'A', 2 => 'B', 3 => 'C'],
            1 => [0 => 'A', 1 => 0, 2 => 1, 3 => 1],
            2 => [0 => 'B', 1 => 0, 2 => 0, 3 => 0],
            3 => [0 => 'C', 1 => 0, 2 => 0, 3 => 0],
        ], $matrix);
    }

    public function testGetAdjencyMetrixIsNotSymmetricForDirectedGraph(): void
    {
        $graph = new Graph(directed: true);
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');

        $matrix = $graph->getAdjencyMetrix();

        $this->assertSame(1, $matrix[1][2]); // A -> B
        $this->assertSame(0, $matrix[2][1]); // B -> A
    }

    public function testGetAdjencyMetrixIsSymmetricForUndirectedGraph(): void
    {
        $graph = new Graph(directed: false);
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'A');

        $matrix = $graph->getAdjencyMetrix();

        $this->assertSame(1, $matrix[1][2]); // A -> B
        $this->assertSame(1, $matrix[2][1]); // B -> A
    }

    public function testGetAdjencyMetrixIsAllZerosForGraphWithNodesButNoEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');

        $matrix = $graph->getAdjencyMetrix();

        $this->assertSame([
            0 => [1 => 'A', 2 => 'B', 3 => 'C'],
            1 => [0 => 'A', 1 => 0, 2 => 0, 3 => 0],
            2 => [0 => 'B', 1 => 0, 2 => 0, 3 => 0],
            3 => [0 => 'C', 1 => 0, 2 => 0, 3 => 0],
        ], $matrix);
    }

    // --- printAdjacencyMatrix ---

    public function testPrintAdjacencyMatrixPrintsHeaderRowWithNodeLabels(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');

        ob_start();
        $graph->printAdjacencyMatrix();
        $output = ob_get_clean();

        $lines = explode(PHP_EOL, $output);
        $this->assertSame('    A  B  C  ', $lines[0]);
        $this->assertSame('   +---------', $lines[1]);
    }

    public function testPrintAdjacencyMatrixPrintsRowLabelsAndBooleanValues(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        $graph->addEdge('A', 'B');
        $graph->addEdge('A', 'C');

        ob_start();
        $graph->printAdjacencyMatrix();
        $output = ob_get_clean();

        $lines = explode(PHP_EOL, $output);
        $this->assertSame('A  | 0  1  1  ', $lines[2]);
        $this->assertSame('B  | 0  0  0  ', $lines[3]);
        $this->assertSame('C  | 0  0  0  ', $lines[4]);
    }

    public function testPrintAdjacencyMatrixForSingleNodeWithoutEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');

        ob_start();
        $graph->printAdjacencyMatrix();
        $output = ob_get_clean();

        $lines = explode(PHP_EOL, $output);
        $this->assertSame('    A  ', $lines[0]);
        $this->assertSame('   +---', $lines[1]);
        $this->assertSame('A  | 0  ', $lines[2]);
    }

    public function testPrintAdjacencyMatrixPrintsAllZeroRowsForNodesWithoutEdges(): void
    {
        $graph = new Graph();
        $graph->addNode('A');
        $graph->addNode('B');

        ob_start();
        $graph->printAdjacencyMatrix();
        $output = ob_get_clean();

        $lines = explode(PHP_EOL, $output);
        $this->assertSame('A  | 0  0  ', $lines[2]);
        $this->assertSame('B  | 0  0  ', $lines[3]);
    }

    public function testPrintAdjacencyMatrixPrintsMessageForEmptyGraphInsteadOfCrashing(): void
    {
        $graph = new Graph();

        ob_start();
        $graph->printAdjacencyMatrix();
        $output = ob_get_clean();

        $this->assertStringContainsString('Graph is empty.', $output);
    }
}
