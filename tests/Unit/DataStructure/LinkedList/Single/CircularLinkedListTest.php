<?php

declare(strict_types=1);

namespace Tests\Unit\DataStructure\LinkedList\Single;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\Constants\ErrorMessages;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\CircularLinkedList;
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedListNode;

class CircularLinkedListTest extends TestCase
{
    /**
     * Asserts the defining invariant of a circular list: walking exactly
     * getLength() steps from the head lands back on the head node, and
     * getTail()'s next node is the head (empty lists have no head/tail).
     */
    private function assertIsCircular(CircularLinkedList $list): void
    {
        $length = $list->getLength();

        if ($length === 0) {
            $this->assertNull($list->getHead());
            return;
        }

        $current = $list->getHead();
        for ($i = 0; $i < $length; $i++) {
            $current = $current->getNext();
        }
        $this->assertSame(
            $list->getHead(),
            $current,
            'Walking length steps from the head should wrap back to the head.'
        );

        $this->assertSame(
            $list->getHead(),
            $list->getTail()->getNext(),
            "The tail's next node should wrap back to the head."
        );
    }

    // --- Factories ---

    public function testEmptyCreatesEmptyList(): void
    {
        $list = CircularLinkedList::empty();

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
        $this->assertSame([], $list->toArrayValues());
    }

    public function testOfCreatesCircularListFromValues(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->assertSame(3, $list->getLength());
        $this->assertSame([1, 2, 3], $list->toArrayValues());
        $this->assertIsCircular($list);
    }

    public function testOfWithEmptyArrayReturnsEmptyList(): void
    {
        $list = CircularLinkedList::of([]);

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
    }

    public function testOfWithSingleValueLinksNodeToItself(): void
    {
        $list = CircularLinkedList::of([1]);

        $this->assertSame(1, $list->getLength());
        $this->assertSame($list->getHead(), $list->getHead()->getNext());
        $this->assertIsCircular($list);
    }

    public function testFromNodesLinksNodesIntoACircle(): void
    {
        $nodes = [
            new SingleLinkedListNode(1),
            new SingleLinkedListNode(2),
            new SingleLinkedListNode(3),
        ];

        $list = CircularLinkedList::fromNodes($nodes);

        $this->assertSame(3, $list->getLength());
        $this->assertSame([1, 2, 3], $list->toArrayValues());
        $this->assertIsCircular($list);
    }

    public function testFromNodesWithEmptyArrayReturnsEmptyList(): void
    {
        $list = CircularLinkedList::fromNodes([]);

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
    }

    public function testFromNodesRejectsNonNodeElements(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CircularLinkedList::fromNodes([new SingleLinkedListNode(1), 'not-a-node']);
    }

    public function testOfObjectsIsAliasForFromNodes(): void
    {
        $nodes = [new SingleLinkedListNode(1), new SingleLinkedListNode(2)];

        $list = CircularLinkedList::ofObjects($nodes);

        $this->assertSame([1, 2], $list->toArrayValues());
        $this->assertIsCircular($list);
    }

    public function testFromIterableBuildsCircularListFromGenerator(): void
    {
        $generator = (function () {
            yield 1;
            yield 2;
            yield 3;
        })();

        $list = CircularLinkedList::fromIterable($generator);

        $this->assertSame(3, $list->getLength());
        $this->assertSame([1, 2, 3], $list->toArrayValues());
        $this->assertIsCircular($list);
    }

    public function testFromIterableWithEmptyIterableReturnsEmptyList(): void
    {
        $list = CircularLinkedList::fromIterable([]);

        $this->assertSame(0, $list->getLength());
        $this->assertNull($list->getHead());
    }

    // --- Iteration ---

    public function testIteratesOverExactlyLengthNodesInOrder(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $values = [];
        foreach ($list as $node) {
            $values[] = $node->getValue();
        }

        // The iterator is bounded by getLength(), not a null-next sentinel
        // (there is none on a circular list), so it must stop after 3 items
        // instead of looping forever.
        $this->assertSame([1, 2, 3], $values);
    }

    public function testIteratesZeroTimesOverEmptyList(): void
    {
        $list = CircularLinkedList::empty();

        $values = [];
        foreach ($list as $node) {
            $values[] = $node->getValue();
        }

        $this->assertSame([], $values);
    }

    // --- Insertion: prepend / append ---

    public function testPrependAddsValueToFrontAndStaysCircular(): void
    {
        $list = CircularLinkedList::of([2, 3]);

        $result = $list->prepend(1);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testPrependOnEmptyListCreatesSelfLinkedNode(): void
    {
        $list = CircularLinkedList::empty();

        $result = $list->prepend(1);

        $this->assertSame([1], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testPrependDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([2, 3]);

        $list->prepend(1);

        $this->assertSame([2, 3], $list->toArrayValues());
    }

    public function testAppendAddsValueToEndAndStaysCircular(): void
    {
        $list = CircularLinkedList::of([1, 2]);

        $result = $list->append(3);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
        $this->assertSame(3, $result->getTail()->getValue());
        $this->assertIsCircular($result);
    }

    public function testAppendOnEmptyListCreatesSelfLinkedNode(): void
    {
        $list = CircularLinkedList::empty();

        $result = $list->append(1);

        $this->assertSame([1], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testAppendDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([1, 2]);

        $list->append(3);

        $this->assertSame([1, 2], $list->toArrayValues());
    }

    // --- Insertion: insert(value, index) ---

    public function testInsertAtStartBehavesLikePrepend(): void
    {
        $list = CircularLinkedList::of([2, 3]);

        $result = $list->insert(1, 0);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testInsertAtEndBehavesLikeAppend(): void
    {
        $list = CircularLinkedList::of([1, 2]);

        $result = $list->insert(3, 2);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
        $this->assertSame(3, $result->getTail()->getValue());
        $this->assertIsCircular($result);
    }

    public function testInsertInMiddleUpdatesValues(): void
    {
        $list = CircularLinkedList::of([1, 2, 4]);

        $result = $list->insert(3, 2);

        $this->assertSame([1, 2, 3, 4], $result->toArrayValues());
    }

    public function testInsertInMiddleStaysCircular(): void
    {
        $list = CircularLinkedList::of([1, 2, 4]);

        $result = $list->insert(3, 2);

        $this->assertIsCircular($result);
    }

    public function testInsertDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $list->insert(99, 1);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testInsertThrowsWhenIndexNegative(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->insert(99, -1);
    }

    public function testInsertThrowsWhenIndexGreaterThanLength(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->insert(99, 4);
    }

    // --- Insertion: insertBeforeNode / insertAfterNode ---

    public function testInsertBeforeNodeInsertsValueImmediatelyBeforeTarget(): void
    {
        $list = CircularLinkedList::of([1, 3]);

        $result = $list->insertBeforeNode(3, 2);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
    }

    public function testInsertBeforeNodeAtHeadPrependsValueAndStaysCircular(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->insertBeforeNode(1, 0);

        $this->assertSame([0, 1, 2, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testInsertBeforeNodeThrowsWhenTargetNotFound(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->insertBeforeNode(99, 0);
    }

    public function testInsertAfterNodeInsertsValueImmediatelyAfterTarget(): void
    {
        $list = CircularLinkedList::of([1, 3]);

        $result = $list->insertAfterNode(1, 2);

        $this->assertSame([1, 2, 3], $result->toArrayValues());
    }

    public function testInsertAfterNodeAtTailAppendsValueAndStaysCircular(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->insertAfterNode(3, 4);

        $this->assertSame([1, 2, 3, 4], $result->toArrayValues());
        $this->assertSame(4, $result->getTail()->getValue());
        $this->assertIsCircular($result);
    }

    public function testInsertAfterNodeThrowsWhenTargetNotFound(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->insertAfterNode(99, 0);
    }

    // --- Removal: removeByValue ---

    public function testRemoveByValueRemovesHeadValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeByValue(1);

        $this->assertSame([2, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testRemoveByValueRemovesTailValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeByValue(3);

        $this->assertSame([1, 2], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testRemoveByValueRemovesMiddleValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeByValue(2);

        $this->assertSame([1, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testRemoveByValueOnSingleNodeListReturnsEmptyList(): void
    {
        $list = CircularLinkedList::of([1]);

        $result = $list->removeByValue(1);

        $this->assertSame(0, $result->getLength());
        $this->assertNull($result->getHead());
    }

    public function testRemoveByValueDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $list->removeByValue(2);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testRemoveByValueThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->removeByValue(1);
    }

    public function testRemoveByValueThrowsWhenValueNotFound(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->removeByValue(99);
    }

    public function testRemoveByValueThrowsWhenSingleNodeValueNotFound(): void
    {
        $list = CircularLinkedList::of([1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->removeByValue(99);
    }

    // --- Removal: removeAt ---

    public function testRemoveAtStartRemovesHead(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeAt(0);

        $this->assertSame([2, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testRemoveAtMiddleRemovesCorrectNode(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeAt(1);

        $this->assertSame([1, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testRemoveAtEndRemovesTail(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeAt(2);

        $this->assertSame([1, 2], $result->toArrayValues());
        $this->assertSame(2, $result->getTail()->getValue());
        $this->assertIsCircular($result);
    }

    public function testRemoveAtDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $list->removeAt(1);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testRemoveAtThrowsWhenIndexNegative(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->removeAt(-1);
    }

    public function testRemoveAtThrowsWhenIndexOutOfBound(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->removeAt(3);
    }

    // --- Removal: removeHead / removeTail ---

    public function testRemoveHeadRemovesFirstValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeHead();

        $this->assertSame([2, 3], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testRemoveHeadOnSingleNodeListReturnsEmptyList(): void
    {
        $list = CircularLinkedList::of([1]);

        $result = $list->removeHead();

        $this->assertSame(0, $result->getLength());
    }

    public function testRemoveHeadThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->removeHead();
    }

    public function testRemoveTailRemovesLastValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->removeTail();

        $this->assertSame([1, 2], $result->toArrayValues());
        $this->assertSame(2, $result->getTail()->getValue());
        $this->assertIsCircular($result);
    }

    public function testRemoveTailOnSingleNodeListReturnsEmptyList(): void
    {
        $list = CircularLinkedList::of([1]);

        $result = $list->removeTail();

        $this->assertSame(0, $result->getLength());
    }

    public function testRemoveTailThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->removeTail();
    }

    // --- Removal: clear / clearAndKeepHead ---

    public function testClearReturnsEmptyList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->clear();

        $this->assertSame(0, $result->getLength());
        $this->assertNull($result->getHead());
    }

    public function testClearThrowsWhenListAlreadyEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->clear();
    }

    public function testClearAndKeepHeadKeepsOnlyTheHeadValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->clearAndKeepHead();

        $this->assertSame([1], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testClearAndKeepHeadThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->clearAndKeepHead();
    }

    // --- Access: get / getTail / contains / indexOf ---

    public function testGetReturnsNodeAtIndex(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->assertSame(2, $list->get(1)->getValue());
    }

    public function testGetThrowsWhenIndexOutOfBound(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::INDEX_OUT_OF_BOUND);

        $list->get(3);
    }

    public function testGetTailReturnsLastNode(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->assertSame(3, $list->getTail()->getValue());
    }

    public function testGetTailThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->getTail();
    }

    public function testContainsReturnsNodeHoldingValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->assertSame(2, $list->contains(2)->getValue());
    }

    public function testContainsThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->contains(1);
    }

    public function testContainsThrowsWhenValueNotFound(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->contains(99);
    }

    public function testIndexOfReturnsPositionOfValue(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->assertSame(2, $list->indexOf(3));
    }

    public function testIndexOfThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->indexOf(1);
    }

    public function testIndexOfThrowsWhenValueNotFound(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::NO_NODE_WITH_THIS_VALUE);

        $list->indexOf(99);
    }

    // --- Transformations ---

    public function testReverseReversesValueOrderAndStaysCircular(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->reverse();

        $this->assertSame([3, 2, 1], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testReverseOnSingleNodeListReturnsSameValue(): void
    {
        $list = CircularLinkedList::of([1]);

        $result = $list->reverse();

        $this->assertSame([1], $result->toArrayValues());
    }

    public function testReverseDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $list->reverse();

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testReverseThrowsWhenListEmpty(): void
    {
        $list = CircularLinkedList::empty();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(ErrorMessages::LINKEDLIST_IS_EMPTY);

        $list->reverse();
    }

    public function testToArrayReturnsNodesInOrder(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $nodes = $list->toArray();

        $this->assertCount(3, $nodes);
        $this->assertContainsOnlyInstancesOf(SingleLinkedListNode::class, $nodes);
        $this->assertSame([1, 2, 3], array_map(fn($node) => $node->getValue(), $nodes));
    }

    public function testToArrayOnEmptyListReturnsEmptyArray(): void
    {
        $this->assertSame([], CircularLinkedList::empty()->toArray());
    }

    public function testToArrayValuesReturnsPlainValues(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testToArrayValuesOnEmptyListReturnsEmptyArray(): void
    {
        $this->assertSame([], CircularLinkedList::empty()->toArrayValues());
    }

    // --- Functional: map / filter / reduce ---

    public function testMapTransformsEveryValueAndStaysCircular(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->map(fn($value) => $value * 2);

        $this->assertSame([2, 4, 6], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testMapOnEmptyListReturnsEmptyList(): void
    {
        $result = CircularLinkedList::empty()->map(fn($value) => $value * 2);

        $this->assertSame(0, $result->getLength());
    }

    public function testMapDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $list->map(fn($value) => $value * 2);

        $this->assertSame([1, 2, 3], $list->toArrayValues());
    }

    public function testFilterKeepsOnlyMatchingValuesAndStaysCircular(): void
    {
        $list = CircularLinkedList::of([1, 2, 3, 4]);

        $result = $list->filter(fn($value) => $value % 2 === 0);

        $this->assertSame([2, 4], $result->toArrayValues());
        $this->assertIsCircular($result);
    }

    public function testFilterOnEmptyListReturnsEmptyList(): void
    {
        $result = CircularLinkedList::empty()->filter(fn($value) => true);

        $this->assertSame(0, $result->getLength());
    }

    public function testFilterWithNoMatchesReturnsEmptyList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3]);

        $result = $list->filter(fn($value) => $value > 99);

        $this->assertSame(0, $result->getLength());
    }

    public function testFilterDoesNotMutateOriginalList(): void
    {
        $list = CircularLinkedList::of([1, 2, 3, 4]);

        $list->filter(fn($value) => $value % 2 === 0);

        $this->assertSame([1, 2, 3, 4], $list->toArrayValues());
    }

    public function testReduceAccumulatesOverAllValues(): void
    {
        $list = CircularLinkedList::of([1, 2, 3, 4]);

        $sum = $list->reduce(fn($carry, $value) => $carry + $value, 0);

        $this->assertSame(10, $sum);
    }

    public function testReduceOnEmptyListReturnsInitialValue(): void
    {
        $result = CircularLinkedList::empty()->reduce(fn($carry, $value) => $carry + $value, 42);

        $this->assertSame(42, $result);
    }

    public function testReduceWithoutInitialValueDefaultsToNull(): void
    {
        $list = CircularLinkedList::of([1]);

        $result = $list->reduce(fn($carry, $value) => ($carry ?? 0) + $value);

        $this->assertSame(1, $result);
    }
}
