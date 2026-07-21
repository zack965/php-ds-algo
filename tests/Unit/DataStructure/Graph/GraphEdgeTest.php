<?php

namespace Tests\Unit\DataStructure\Graph;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Graph\GraphEdge;

class GraphEdgeTest extends TestCase
{
    public function testGetSourceNodeReturnsSourceNode(): void
    {
        $edge = new GraphEdge('A', 'B');

        $this->assertSame('A', $edge->getSourceNode());
    }

    public function testGetDestinationNodeReturnsDestinationNode(): void
    {
        $edge = new GraphEdge('A', 'B');

        $this->assertSame('B', $edge->getDestinationNode());
    }

    public function testGetWeightReturnsConfiguredWeight(): void
    {
        $edge = new GraphEdge('A', 'B', 5);

        $this->assertSame(5, $edge->getWeight());
    }

    public function testGetWeightReturnsFloatWeight(): void
    {
        $edge = new GraphEdge('A', 'B', 2.5);

        $this->assertSame(2.5, $edge->getWeight());
    }

    public function testGetWeightReturnsNullWhenEdgeIsUnweighted(): void
    {
        $edge = new GraphEdge('A', 'B');

        $this->assertNull($edge->getWeight());
    }

    public function testGetMetadataDefaultsToEmptyArray(): void
    {
        $edge = new GraphEdge('A', 'B');

        $this->assertSame([], $edge->getMetadata());
    }

    public function testGetMetadataReturnsConfiguredMetadata(): void
    {
        $edge = new GraphEdge('A', 'B', 1, ['label' => 'road']);

        $this->assertSame(['label' => 'road'], $edge->getMetadata());
    }
}
