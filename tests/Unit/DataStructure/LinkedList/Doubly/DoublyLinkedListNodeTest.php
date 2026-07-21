<?php

namespace Tests\Unit\DataStructure\LinkedList\Doubly;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedListNode;

class DoublyLinkedListNodeTest extends TestCase
{
    public function testGetValueReturnsConstructedValue(): void
    {
        $node = new DoublyLinkedListNode(1);

        $this->assertSame(1, $node->getValue());
    }

    public function testGetNextAndGetPreviousDefaultToNull(): void
    {
        $node = new DoublyLinkedListNode(1);

        $this->assertNull($node->getNext());
        $this->assertNull($node->getPrevious());
    }

    public function testConstructorAcceptsNextAndPrevious(): void
    {
        $next = new DoublyLinkedListNode(2);
        $previous = new DoublyLinkedListNode(0);
        $node = new DoublyLinkedListNode(1, $next, $previous);

        $this->assertSame($next, $node->getNext());
        $this->assertSame($previous, $node->getPrevious());
    }

    public function testSetNextUpdatesNextNode(): void
    {
        $node = new DoublyLinkedListNode(1);
        $next = new DoublyLinkedListNode(2);

        $node->setNext($next);

        $this->assertSame($next, $node->getNext());
    }

    public function testSetNextAcceptsNull(): void
    {
        $next = new DoublyLinkedListNode(2);
        $node = new DoublyLinkedListNode(1, $next);

        $node->setNext(null);

        $this->assertNull($node->getNext());
    }

    public function testSetPreviousUpdatesPreviousNode(): void
    {
        $node = new DoublyLinkedListNode(1);
        $previous = new DoublyLinkedListNode(0);

        $node->setPrevious($previous);

        $this->assertSame($previous, $node->getPrevious());
    }

    public function testSetPreviousAcceptsNullToClearTheLink(): void
    {
        $previous = new DoublyLinkedListNode(0);
        $node = new DoublyLinkedListNode(1, null, $previous);

        $node->setPrevious(null);

        $this->assertNull($node->getPrevious());
    }

    public function testToStringWithoutNextOrPrevious(): void
    {
        $node = new DoublyLinkedListNode(1);

        $this->assertSame('Node(value=1, previous=NULL, next=NULL)', (string) $node);
    }

    public function testToStringWithNextAndPrevious(): void
    {
        $next = new DoublyLinkedListNode(2);
        $previous = new DoublyLinkedListNode(0);
        $node = new DoublyLinkedListNode(1, $next, $previous);

        $this->assertSame('Node(value=1, previous=0, next=2)', (string) $node);
    }
}
