<?php

namespace Tests\Unit\DataStructure\Heap;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Heap\PriorityQueueNode;

class PriorityQueueNodeTest extends TestCase
{
    public function testGetValueReturnsConstructedValue(): void
    {
        $node = new PriorityQueueNode('task-a', 5);

        $this->assertSame('task-a', $node->getValue());
    }

    public function testGetPriorityReturnsConstructedPriority(): void
    {
        $node = new PriorityQueueNode('task-a', 5);

        $this->assertSame(5, $node->getPriority());
    }

    public function testAcceptsFloatPriority(): void
    {
        $node = new PriorityQueueNode('task-b', 2.5);

        $this->assertSame(2.5, $node->getPriority());
    }

    public function testAcceptsArbitraryMixedValue(): void
    {
        $payload = ['id' => 1, 'name' => 'alpha'];
        $node = new PriorityQueueNode($payload, 1);

        $this->assertSame($payload, $node->getValue());
    }
}
