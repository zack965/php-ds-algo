<?php

namespace Tests\Unit\DataStructure\LinkedList\Single;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedListNode;

class SingleLinkedListNodeTest extends TestCase
{
    public function testGetValueReturnsConstructedValue(): void
    {
        $node = new SingleLinkedListNode(1);

        $this->assertSame(1, $node->getValue());
    }

    public function testSetValueUpdatesValue(): void
    {
        $node = new SingleLinkedListNode(1);

        $node->setValue(2);

        $this->assertSame(2, $node->getValue());
    }

    public function testGetNextDefaultsToNull(): void
    {
        $node = new SingleLinkedListNode(1);

        $this->assertNull($node->getNext());
    }

    public function testConstructorAcceptsNextNode(): void
    {
        $next = new SingleLinkedListNode(2);
        $node = new SingleLinkedListNode(1, $next);

        $this->assertSame($next, $node->getNext());
    }

    public function testSetNextUpdatesNextNode(): void
    {
        $node = new SingleLinkedListNode(1);
        $next = new SingleLinkedListNode(2);

        $node->setNext($next);

        $this->assertSame($next, $node->getNext());
    }

    public function testSetNextAcceptsNull(): void
    {
        $next = new SingleLinkedListNode(2);
        $node = new SingleLinkedListNode(1, $next);

        $node->setNext(null);

        $this->assertNull($node->getNext());
    }

    public function testToStringWithoutNextNode(): void
    {
        $node = new SingleLinkedListNode(1);

        $this->assertSame('Node(value=1, next=NULL)', (string) $node);
    }

    public function testToStringWithNextNode(): void
    {
        $next = new SingleLinkedListNode(2);
        $node = new SingleLinkedListNode(1, $next);

        $this->assertSame('Node(value=1, next=2)', (string) $node);
    }
}
