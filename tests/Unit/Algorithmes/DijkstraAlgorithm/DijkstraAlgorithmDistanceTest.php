<?php

namespace Tests\Unit\Algorithmes\DijkstraAlgorithm;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Algorithmes\DijkstraAlgorithm\DijkstraAlgorithmDistance;

class DijkstraAlgorithmDistanceTest extends TestCase
{
    public function testDefaultsMatchAnUnvisitedUnreachedNode(): void
    {
        $distance = new DijkstraAlgorithmDistance();

        $this->assertFalse($distance->isVisited());
        $this->assertNull($distance->getValue());
        $this->assertSame(INF, $distance->getShortestDistance());
        $this->assertNull($distance->getPreviousNode());
    }

    public function testConstructorAcceptsExplicitValues(): void
    {
        $distance = new DijkstraAlgorithmDistance(true, 'A', 5, 'B');

        $this->assertTrue($distance->isVisited());
        $this->assertSame('A', $distance->getValue());
        $this->assertSame(5, $distance->getShortestDistance());
        $this->assertSame('B', $distance->getPreviousNode());
    }

    public function testSetIsVisitedUpdatesTheFlag(): void
    {
        $distance = new DijkstraAlgorithmDistance();

        $distance->setIsVisited(true);

        $this->assertTrue($distance->isVisited());
    }

    public function testSetValueReplacesStoredValue(): void
    {
        $distance = new DijkstraAlgorithmDistance();

        $distance->setValue('node-A');

        $this->assertSame('node-A', $distance->getValue());
    }

    public function testSetShortestDistanceAcceptsInt(): void
    {
        $distance = new DijkstraAlgorithmDistance();

        $distance->setShortestDistance(10);

        $this->assertSame(10, $distance->getShortestDistance());
    }

    public function testSetShortestDistanceAcceptsFloat(): void
    {
        $distance = new DijkstraAlgorithmDistance();

        $distance->setShortestDistance(2.5);

        $this->assertSame(2.5, $distance->getShortestDistance());
    }

    public function testSetShortestDistanceAcceptsInfinity(): void
    {
        $distance = new DijkstraAlgorithmDistance();
        $distance->setShortestDistance(10);

        $distance->setShortestDistance(INF);

        $this->assertSame(INF, $distance->getShortestDistance());
    }

    public function testSetPreviousNodeReplacesStoredPreviousNode(): void
    {
        $distance = new DijkstraAlgorithmDistance();

        $distance->setPreviousNode('node-A');

        $this->assertSame('node-A', $distance->getPreviousNode());
    }

    public function testAcceptsArbitraryMixedValueAndPreviousNode(): void
    {
        $payload = ['id' => 1];
        $distance = new DijkstraAlgorithmDistance(false, $payload, 0, $payload);

        $this->assertSame($payload, $distance->getValue());
        $this->assertSame($payload, $distance->getPreviousNode());
    }
}
