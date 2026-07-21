<?php

namespace Tests\Unit\DataStructure\LinkedList\Doubly;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Constants\ErrorMessages;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedList;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedListNode;

class DoublyLinkedListTest extends TestCase
{
    // --- Factories ---

    public function testEmptyCreatesEmptyList(): void
    {
        $list = DoublyLinkedList::empty();

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
        $this->assertSame([], $list->toArrayValues());
    }

    public function testOfCreatesListFromValues(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->assertSame(3, $list->getLength());
        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }


    public function testOfWithEmptyArrayReturnsEmptyList(): void
    {
        $list = DoublyLinkedList::of([]);

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
    }
    public function testOfLinksPreviousPointersBackward(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $head = $list->getHead();
        $second = $head->getNext();
        $third = $second->getNext();

        $this->assertNull($head->getPrevious());
        $this->assertSame($head, $second->getPrevious());
        $this->assertSame($second, $third->getPrevious());
    }
    public function testFromNodesLinksNodesTogether(): void
    {
        $nodes = [
            new DoublyLinkedListNode(1),
            new DoublyLinkedListNode(2),
            new DoublyLinkedListNode(3),
        ];

        $list = DoublyLinkedList::fromNodes($nodes);

        $this->assertSame(3, $list->getLength());
        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }


    public function testFromNodesLinksPreviousPointersBackward(): void
    {
        $nodes = [
            new DoublyLinkedListNode(1),
            new DoublyLinkedListNode(2),
            new DoublyLinkedListNode(3),
        ];

        $list = DoublyLinkedList::fromNodes($nodes);

        $head = $list->getHead();
        $second = $head->getNext();
        $third = $second->getNext();

        $this->assertNull($head->getPrevious());
        $this->assertSame($head, $second->getPrevious());
        $this->assertSame($second, $third->getPrevious());
    }

    public function testFromNodesWithEmptyArrayReturnsEmptyList(): void
    {
        $list = DoublyLinkedList::fromNodes([]);

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
    }


    public function testFromNodesRejectsNonNodeElements(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DoublyLinkedList::fromNodes([new DoublyLinkedListNode(1), 'not-a-node']);
    }

    public function testOfObjectsIsAliasForFromNodes(): void
    {
        $nodes = [new DoublyLinkedListNode(1), new DoublyLinkedListNode(2)];

        $list = DoublyLinkedList::ofObjects($nodes);

        $this->assertSame([1, 2], $list->toArrayValues());
    }


    public function testFromIterableBuildsListFromValues(): void
    {
        $list = DoublyLinkedList::fromIterable([1, 2, 3]);

        $this->assertSame(3, $list->getLength());
        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testFromIterableWithEmptyIterableReturnsEmptyList(): void
    {
        $list = DoublyLinkedList::fromIterable([]);

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
    }


    // --- Iteration ---

    public function testIteratesOverNodesInOrder(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $values = [];
        foreach ($list as $node) {
            $values[] = $node->getValue();
        }

        $this->assertSame([1, 2, 3], $values);
    }

    // --- Insertion: prepend / append / insert ---

    public function testPrependAddsValueToFront(): void
    {
        $list = DoublyLinkedList::of([2, 3]);

        $result = $list->prepend(1);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
        $this->assertSame(3, $result->getLength());
    }


    public function testPrependOnEmptyListCreatesSingleNodeList(): void
    {
        $list = DoublyLinkedList::empty();

        $result = $list->prepend(1);

        $this->assertSame([1], $result->toArrayValues());
        $this->assertSame(1, $result->getLength());
    }

    public function testPrependSetsPreviousPointerOnOldHead(): void
    {
        $list = DoublyLinkedList::of([2, 3]);

        $result = $list->prepend(1);

        $newHead = $result->getHead();
        $this->assertSame($newHead, $newHead->getNext()->getPrevious());
    }


    public function testPrependDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([2, 3]);

        $list->prepend(1);

        $this->assertSame([2, 3], $list->toArrayValues());
        $this->assertSame(2, $list->getLength());
    }

    public function testAppendAddsValueToEnd(): void
    {
        $list = DoublyLinkedList::of([1, 2]);

        $result = $list->append(3);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
        $this->assertSame(3, $result->getLength());
    }

    public function testAppendOnEmptyListCreatesSingleNodeList(): void
    {
        $list = DoublyLinkedList::empty();

        $result = $list->append(1);

        $this->assertSame([1], $result->toArrayValues());
        $this->assertSame(1, $result->getLength());
    }

    public function testAppendSetsPreviousPointerOnNewTail(): void
    {
        $list = DoublyLinkedList::of([1, 2]);

        $result = $list->append(3);

        $tail = $result->getTail();
        $this->assertSame(3, $tail->getValue());
        $this->assertSame(2, $tail->getPrevious()->getValue());
    }

    public function testAppendDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([1, 2]);

        $list->append(3);

        $this->assertSame([1, 2], $list->toArrayValues());
        $this->assertSame(2, $list->getLength());
    }

    public function testInsertAtStartBehavesLikePrepend(): void
    {
        $list = DoublyLinkedList::of([2, 3]);

        $result = $list->insert(1, 0);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
    }

    public function testInsertAtEndBehavesLikeAppend(): void
    {
        $list = DoublyLinkedList::of([1, 2]);

        $result = $list->insert(3, 2);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
    }

    public function testInsertInMiddle(): void
    {
        $list = DoublyLinkedList::of([1, 3]);

        $result = $list->insert(2, 1);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
        $this->assertSame(3, $result->getLength());
    }


    public function testInsertInMiddleLinksPreviousPointerOfInsertedNode(): void
    {
        $list = DoublyLinkedList::of([1, 3]);

        $result = $list->insert(2, 1);

        $middle = $result->get(1);
        $this->assertSame(1, $middle->getPrevious()->getValue());
    }

    public function testInsertDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([1, 3]);

        $list->insert(2, 1);

        $this->assertSame([1, 3], $list->toArrayValues());
        $this->assertSame(2, $list->getLength());
    }

    public function testInsertThrowsWhenIndexNegative(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->insert(99, -1);
    }

    public function testInsertThrowsWhenIndexGreaterThanLength(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->insert(99, 4);
    }

    // --- insertBeforeNode / insertAfterNode ---

    public function testInsertBeforeNodeInsertsValueImmediatelyBeforeTarget(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3, 4]);

        $result = $list->insertBeforeNode(3, 99);

        $this->assertSame([1, 2, 99, 3, 4], $result->toArrayValues());
        $this->assertSame(5, $result->getLength());
    }

    public function testInsertBeforeNodeAtHeadPrependsValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->insertBeforeNode(1, 0);

        $this->assertSame([0, 1, 2, 3], $result->toArrayValues());
    }

    public function testInsertBeforeNodeDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $list->insertBeforeNode(2, 99);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testInsertBeforeNodeThrowsWhenTargetNotFound(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->insertBeforeNode(99, 0);
    }

    public function testInsertAfterNodeInsertsValueImmediatelyAfterTarget(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3, 4]);

        $result = $list->insertAfterNode(2, 99);

        $this->assertSame([1, 2, 99, 3, 4], $result->toArrayValues());
        $this->assertSame(5, $result->getLength());
    }

    public function testInsertAfterNodeAtTailAppendsValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->insertAfterNode(3, 99);

        $this->assertSame([1, 2, 3, 99], $result->toArrayValues());
    }

    public function testInsertAfterNodeDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $list->insertAfterNode(2, 99);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testInsertAfterNodeThrowsWhenTargetNotFound(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->insertAfterNode(99, 0);
    }

    // --- Removal ---

    public function testRemoveByValueRemovesHeadValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeByValue(1);

        $this->assertSame([2, 3], $result->toArrayValues());
        $this->assertSame(2, $result->getLength());
    }

    public function testRemoveByValueRemovesMiddleValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeByValue(2);

        $this->assertSame([1, 3], $result->toArrayValues());
    }



    public function testRemoveByValueRemovesTailValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeByValue(3);

        $this->assertSame([1, 2], $result->toArrayValues());
    }

    public function testRemoveByValueDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $list->removeByValue(2);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testRemoveByValueThrowsWhenValueNotFound(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->removeByValue(99);
    }

    public function testRemoveByValueThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->removeByValue(1);
    }

    public function testClearEmptiesTheList(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->clear();

        $this->assertSame(0, $result->getLength());
        $this->assertNull($result->getHead());
    }

    public function testClearThrowsWhenListAlreadyEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->clear();
    }



    public function testClearAndKeepHeadKeepsOnlyFirstValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->clearAndKeepHead();

        $this->assertSame(1, $result->getLength());
        $this->assertSame([1], $result->toArrayValues());
    }

    public function testClearAndKeepHeadThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->clearAndKeepHead();
    }

    public function testRemoveAtRemovesHead(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeAt(0);

        $this->assertSame([2, 3], $result->toArrayValues());
    }

    public function testRemoveAtRemovesMiddleElement(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeAt(1);

        $this->assertSame([1, 3], $result->toArrayValues());
        $this->assertSame(2, $result->getLength());
    }

    public function testRemoveAtRemovesLastElement(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeAt(2);

        $this->assertSame([1, 2], $result->toArrayValues());
    }

    public function testRemoveAtDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $list->removeAt(1);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testRemoveAtThrowsForNegativeIndex(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->removeAt(-1);
    }


    public function testRemoveAtThrowsForIndexEqualToLength(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->removeAt(3);
    }

    public function testRemoveHeadDropsFirstValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeHead();

        $this->assertSame([2, 3], $result->toArrayValues());
        $this->assertSame(2, $result->getLength());
    }

    public function testRemoveHeadThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->removeHead();
    }

    public function testRemoveTailDropsLastValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->removeTail();

        $this->assertSame([1, 2], $result->toArrayValues());
        $this->assertSame(2, $result->getLength());
    }


    public function testRemoveTailOnSingleElementListReturnsEmptyList(): void
    {
        $list = DoublyLinkedList::of([1]);

        $result = $list->removeTail();

        $this->assertSame(0, $result->getLength());
        $this->assertNull($result->getHead());
    }

    public function testRemoveTailDoesNotMutateOriginalList(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $list->removeTail();

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testRemoveTailThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->removeTail();
    }


    // --- Access ---

    public function testGetReturnsNodeAtIndex(): void
    {
        $list = DoublyLinkedList::of([10, 20, 30]);

        $this->assertSame(20, $list->get(1)->getValue());
    }

    public function testGetThrowsForNegativeIndex(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->get(-1);
    }

    public function testGetThrowsForIndexOutOfBounds(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->get(3);
    }

    public function testGetTailReturnsLastNode(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->assertSame(3, $list->getTail()->getValue());
    }

    public function testGetTailThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->getTail();
    }

    public function testContainsReturnsMatchingNode(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->assertSame(2, $list->contains(2)->getValue());
    }

    public function testContainsThrowsWhenValueNotFound(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->contains(99);
    }

    public function testContainsThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->contains(1);
    }

    public function testIndexOfReturnsMatchingIndex(): void
    {
        $list = DoublyLinkedList::of([10, 20, 30]);

        $this->assertSame(2, $list->indexOf(30));
    }

    public function testIndexOfThrowsWhenValueNotFound(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->indexOf(99);
    }



    public function testIndexOfThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->indexOf(1);
    }

    // --- Transformations ---

    public function testReverseFlipsOrderOfValues(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->reverse();

        $this->assertSame([3, 2, 1], $result->toArrayValues());
        $this->assertSame(3, $result->getLength());
    }

    public function testReverseThrowsWhenListEmpty(): void
    {
        $list = DoublyLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->reverse();
    }

    public function testToArrayReturnsNodesInOrder(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $nodes = $list->toArray();

        $this->assertCount(3, $nodes);
        $this->assertContainsOnlyInstancesOf(DoublyLinkedListNode::class, $nodes);
        $this->assertSame([1, 2, 3], array_map(fn($node) => $node->getValue(), $nodes));
    }

    public function testToArrayOnEmptyListReturnsEmptyArray(): void
    {
        $list = DoublyLinkedList::empty();

        $this->assertSame([], $list->toArray());
    }

    public function testToArrayValuesOnEmptyListReturnsEmptyArray(): void
    {
        $list = DoublyLinkedList::empty();

        $this->assertSame([], $list->toArrayValues());
    }


    // --- Functional ---

    public function testMapTransformsEachValue(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3]);

        $result = $list->map(fn($value) => $value * 2);

        $this->assertSame([2, 4, 6], $result->toArrayValues());
    }

    public function testMapOnEmptyListReturnsEmptyList(): void
    {
        $list = DoublyLinkedList::empty();

        $result = $list->map(fn($value) => $value * 2);

        $this->assertSame(0, $result->getLength());
    }

    public function testFilterKeepsOnlyMatchingValues(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3, 4, 5]);

        $result = $list->filter(fn($value) => $value % 2 === 0);

        $this->assertSame([2, 4], $result->toArrayValues());
    }


    public function testFilterOnEmptyListReturnsEmptyList(): void
    {
        $list = DoublyLinkedList::empty();

        $result = $list->filter(fn($value) => true);

        $this->assertSame(0, $result->getLength());
    }

    public function testReduceAccumulatesOverValues(): void
    {
        $list = DoublyLinkedList::of([1, 2, 3, 4]);

        $sum = $list->reduce(fn($carry, $value) => $carry + $value, 0);

        $this->assertSame(10, $sum);
    }

    public function testReduceOnEmptyListReturnsInitialValue(): void
    {
        $list = DoublyLinkedList::empty();

        $result = $list->reduce(fn($carry, $value) => $carry + $value, 42);

        $this->assertSame(42, $result);
    }
}
