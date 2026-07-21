<?php

namespace Tests\Unit\DataStructure\Graph;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Graph\GraphNode;

class GraphNodeTest extends TestCase
{
    public function testGetValueReturnsIntValue(): void
    {
        $node = new GraphNode(1);

        $this->assertSame(1, $node->getValue());
    }

    public function testGetValueReturnsStringValue(): void
    {
        $node = new GraphNode('A');

        $this->assertSame('A', $node->getValue());
    }

    public function testValuePropertyIsReadable(): void
    {
        $node = new GraphNode('A');

        $this->assertSame('A', $node->value);
    }
}
