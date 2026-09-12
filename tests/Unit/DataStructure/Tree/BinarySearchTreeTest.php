<?php

namespace Tests\Unit\DataStructure\Tree;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Tree\BinarySearchTree;
use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTreeNode;

class BinarySearchTreeTest extends TestCase
{
    // --- Construction ---

    public function testConstructWithNoArgumentsIsEmpty(): void
    {
        $bst = new BinarySearchTree();

        $this->assertTrue($bst->isEmpty());
        $this->assertNull($bst->getRoot());
        $this->assertSame(0, $bst->count());
    }

    public function testConstructWithEmptyArrayIsEmpty(): void
    {
        $bst = new BinarySearchTree([]);

        $this->assertTrue($bst->isEmpty());
        $this->assertSame(0, $bst->count());
    }

    public function testConstructWithValuesInsertsThemAll(): void
    {
        $bst = new BinarySearchTree([5, 3, 8, 1, 4]);

        $this->assertSame(5, $bst->count());
        $this->assertSame([1, 3, 4, 5, 8], $bst->inOrder());
    }

    public function testConstructIgnoresDuplicateValues(): void
    {
        $bst = new BinarySearchTree([5, 3, 5, 3, 8]);

        $this->assertSame(3, $bst->count());
        $this->assertSame([3, 5, 8], $bst->inOrder());
    }

    public function testFromArrayBuildsEquivalentTree(): void
    {
        $bst = BinarySearchTree::fromArray([5, 3, 8, 1, 4]);

        $this->assertSame([1, 3, 4, 5, 8], $bst->inOrder());
    }

    // --- Inherited AbstractTree behaviour: getRoot / isEmpty / clear / count ---

    public function testGetRootReturnsTheRootNode(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $this->assertInstanceOf(BinaryTreeNode::class, $bst->getRoot());
        $this->assertSame(5, $bst->getRoot()->getValue());
    }

    public function testIsEmptyIsFalseAfterInsert(): void
    {
        $bst = new BinarySearchTree([5]);

        $this->assertFalse($bst->isEmpty());
    }

    public function testClearResetsTheTreeToEmpty(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $bst->clear();

        $this->assertTrue($bst->isEmpty());
        $this->assertNull($bst->getRoot());
        $this->assertSame(0, $bst->count());
        $this->assertSame([], $bst->inOrder());
    }

    public function testCountReflectsNumberOfInsertedValues(): void
    {
        $bst = new BinarySearchTree([5, 3, 8, 1]);

        $this->assertSame(4, $bst->count());
        $this->assertCount(4, $bst);
    }

    public function testCountDoesNotIncreaseForIgnoredDuplicateInsert(): void
    {
        $bst = new BinarySearchTree([5, 3]);

        $bst->insert(5);

        $this->assertSame(2, $bst->count());
    }

    // --- Inherited AbstractTree behaviour: getHeight ---

    public function testGetHeightOfEmptyTreeIsMinusOne(): void
    {
        $this->assertSame(-1, (new BinarySearchTree())->getHeight());
    }

    public function testGetHeightOfSingleNodeTreeIsZero(): void
    {
        $this->assertSame(0, (new BinarySearchTree([5]))->getHeight());
    }

    public function testGetHeightOfBalancedTree(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(2, $bst->getHeight());
    }

    public function testGetHeightOfSkewedTreeEqualsChainLength(): void
    {
        // Strictly increasing inserts degrade a BST into a linked list.
        $bst = new BinarySearchTree([1, 2, 3, 4, 5]);

        $this->assertSame(4, $bst->getHeight());
    }

    // --- Inherited AbstractTree behaviour: contains ---

    public function testContainsReturnsTrueForPresentValue(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $this->assertTrue($bst->contains(3));
    }

    public function testContainsReturnsFalseForAbsentValue(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $this->assertFalse($bst->contains(99));
    }

    public function testContainsOnEmptyTreeReturnsFalse(): void
    {
        $this->assertFalse((new BinarySearchTree())->contains(1));
    }

    // --- Inherited AbstractTree behaviour: levelOrder / toArray / getIterator ---

    public function testLevelOrderReturnsValuesBreadthFirst(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame([50, 30, 70, 20, 40, 60, 80], $bst->levelOrder());
    }

    public function testLevelOrderOnEmptyTreeReturnsEmptyArray(): void
    {
        $this->assertSame([], (new BinarySearchTree())->levelOrder());
    }

    public function testToArrayIsAnAliasForLevelOrder(): void
    {
        $bst = new BinarySearchTree([50, 30, 70]);

        $this->assertSame($bst->levelOrder(), $bst->toArray());
    }

    public function testGetIteratorYieldsValuesInOrder(): void
    {
        $bst = new BinarySearchTree([5, 3, 8, 1, 4]);

        $values = [];
        foreach ($bst as $value) {
            $values[] = $value;
        }

        $this->assertSame([1, 3, 4, 5, 8], $values);
    }

    public function testGetIteratorOnEmptyTreeYieldsNothing(): void
    {
        $values = [];
        foreach (new BinarySearchTree() as $value) {
            $values[] = $value;
        }

        $this->assertSame([], $values);
    }

    // --- insert ---

    public function testInsertReturnsSameInstanceForChaining(): void
    {
        $bst = new BinarySearchTree();

        $result = $bst->insert(5)->insert(3)->insert(8);

        $this->assertSame($bst, $result);
        $this->assertSame([3, 5, 8], $bst->inOrder());
    }

    public function testInsertMaintainsBstOrderingProperty(): void
    {
        $bst = new BinarySearchTree();
        $bst->insert(50)->insert(30)->insert(70)->insert(20)->insert(40);

        $root = $bst->getRoot();
        $this->assertSame(50, $root->getValue());
        $this->assertSame(30, $root->getLeft()->getValue());
        $this->assertSame(70, $root->getRight()->getValue());
        $this->assertSame(20, $root->getLeft()->getLeft()->getValue());
        $this->assertSame(40, $root->getLeft()->getRight()->getValue());
    }

    public function testInsertIgnoresDuplicateAndKeepsSizeStable(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $bst->insert(3);

        $this->assertSame(3, $bst->count());
        $this->assertSame([3, 5, 8], $bst->inOrder());
    }

    // --- search ---

    public function testSearchFindsExistingValue(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $node = $bst->search(3);

        $this->assertInstanceOf(BinaryTreeNode::class, $node);
        $this->assertSame(3, $node->getValue());
    }

    public function testSearchReturnsNullForMissingValue(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $this->assertNull($bst->search(99));
    }

    public function testSearchOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->search(1));
    }

    // --- min / max ---

    public function testMinReturnsSmallestValueNode(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40]);

        $this->assertSame(20, $bst->min()->getValue());
    }

    public function testMaxReturnsLargestValueNode(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40]);

        $this->assertSame(70, $bst->max()->getValue());
    }

    public function testMinOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->min());
    }

    public function testMaxOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->max());
    }

    public function testMinOnSingleNodeTreeReturnsThatNode(): void
    {
        $this->assertSame(5, (new BinarySearchTree([5]))->min()->getValue());
    }

    // --- predecessor / successor ---

    public function testPredecessorOfExistingValueWithLeftSubtreeIsMaxOfLeftSubtree(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 35, 45]);

        // predecessor of 50 walks into its left subtree (rooted at 30) and
        // takes that subtree's max.
        $this->assertSame(45, $bst->predecessor(50)->getValue());
    }

    public function testPredecessorOfExistingLeafFallsBackToLastRightTurn(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40]);

        $this->assertSame(30, $bst->predecessor(40)->getValue());
    }

    public function testPredecessorOfMinimumValueIsNull(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20]);

        $this->assertNull($bst->predecessor(20));
    }

    public function testPredecessorOfNonExistentValueBetweenNodes(): void
    {
        $bst = new BinarySearchTree([50, 30, 70]);

        $this->assertSame(30, $bst->predecessor(45)->getValue());
    }

    public function testPredecessorOfValueAboveMaxReturnsMax(): void
    {
        $bst = new BinarySearchTree([50, 30, 70]);

        $this->assertSame(70, $bst->predecessor(999)->getValue());
    }

    public function testPredecessorOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->predecessor(5));
    }

    public function testSuccessorOfExistingValueWithRightSubtreeIsMinOfRightSubtree(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 60, 80, 55, 65]);

        $this->assertSame(55, $bst->successor(50)->getValue());
    }

    public function testSuccessorOfMaximumValueIsNull(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 80]);

        $this->assertNull($bst->successor(80));
    }

    public function testSuccessorOfValueBelowMinReturnsMin(): void
    {
        $bst = new BinarySearchTree([50, 30, 70]);

        $this->assertSame(30, $bst->successor(-999)->getValue());
    }

    public function testSuccessorOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->successor(5));
    }

    // --- floor / ceiling ---

    public function testFloorOfExactMatchReturnsThatValue(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(20, $bst->floor(20));
    }

    public function testFloorOfValueBetweenNodesReturnsTheLargerLowerNeighbor(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(40, $bst->floor(45));
    }

    public function testFloorBelowMinimumReturnsNull(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20]);

        $this->assertNull($bst->floor(10));
    }

    public function testFloorOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->floor(5));
    }

    public function testCeilingOfExactMatchReturnsThatValue(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(80, $bst->ceiling(80));
    }

    public function testCeilingOfValueBetweenNodesReturnsTheSmallerHigherNeighbor(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(50, $bst->ceiling(45));
    }

    public function testCeilingAboveMaximumReturnsNull(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 80]);

        $this->assertNull($bst->ceiling(90));
    }

    public function testCeilingOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->ceiling(5));
    }

    // --- findClosest ---

    public function testFindClosestReturnsExactMatchWhenPresent(): void
    {
        $bst = new BinarySearchTree([10, 6, 14]);

        $this->assertSame(10, $bst->findClosest(10));
    }

    public function testFindClosestReturnsNearestValueBelow(): void
    {
        $bst = new BinarySearchTree([10, 6, 14]);

        $this->assertSame(6, $bst->findClosest(5));
    }

    public function testFindClosestReturnsNearestValueAbove(): void
    {
        $bst = new BinarySearchTree([10, 6, 14]);

        $this->assertSame(14, $bst->findClosest(20));
    }

    public function testFindClosestOnExactDistanceTieFavorsTheShallowerNode(): void
    {
        // 8 is equidistant (2) from 6 and 10, but 10 is the root (visited
        // first) and the comparison only updates on a strictly smaller
        // distance, so the tie keeps the shallower node.
        $bst = new BinarySearchTree([10, 6, 14]);

        $this->assertSame(10, $bst->findClosest(8));
    }

    public function testFindClosestOnSingleNodeTreeReturnsThatValue(): void
    {
        $this->assertSame(5, (new BinarySearchTree([5]))->findClosest(999));
    }

    public function testFindClosestOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->findClosest(5));
    }

    // --- rangeSearch / countInRange ---

    public function testRangeSearchReturnsValuesWithinInclusiveRangeInSortedOrder(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame([30, 40, 50, 60], $bst->rangeSearch(25, 65));
    }

    public function testRangeSearchIncludesBoundaryValues(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame([30, 40, 50, 60, 70], $bst->rangeSearch(30, 70));
    }

    public function testRangeSearchWithNoValuesInRangeReturnsEmptyArray(): void
    {
        $bst = new BinarySearchTree([50, 30, 70]);

        $this->assertSame([], $bst->rangeSearch(1000, 2000));
    }

    public function testRangeSearchOnEmptyTreeReturnsEmptyArray(): void
    {
        $this->assertSame([], (new BinarySearchTree())->rangeSearch(0, 100));
    }

    public function testCountInRangeMatchesRangeSearchCount(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(4, $bst->countInRange(25, 65));
    }

    public function testCountInRangeOnEmptyTreeIsZero(): void
    {
        $this->assertSame(0, (new BinarySearchTree())->countInRange(0, 100));
    }

    // --- inOrder ---

    public function testInOrderReturnsValuesInAscendingOrder(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame([20, 30, 40, 50, 60, 70, 80], $bst->inOrder());
    }

    public function testInOrderOnEmptyTreeReturnsEmptyArray(): void
    {
        $this->assertSame([], (new BinarySearchTree())->inOrder());
    }

    // --- kthSmallest / kthLargest ---

    public function testKthSmallestReturnsCorrectValue(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(20, $bst->kthSmallest(1));
        $this->assertSame(40, $bst->kthSmallest(3));
        $this->assertSame(80, $bst->kthSmallest(7));
    }

    public function testKthSmallestOutOfBoundsReturnsNull(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $this->assertNull($bst->kthSmallest(0));
        $this->assertNull($bst->kthSmallest(-1));
        $this->assertNull($bst->kthSmallest(4));
    }

    public function testKthSmallestOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->kthSmallest(1));
    }

    public function testKthLargestReturnsCorrectValue(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(80, $bst->kthLargest(1));
        $this->assertSame(60, $bst->kthLargest(3));
        $this->assertSame(20, $bst->kthLargest(7));
    }

    public function testKthLargestOutOfBoundsReturnsNull(): void
    {
        $bst = new BinarySearchTree([5, 3, 8]);

        $this->assertNull($bst->kthLargest(0));
        $this->assertNull($bst->kthLargest(-1));
        $this->assertNull($bst->kthLargest(4));
    }

    public function testKthLargestOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->kthLargest(1));
    }

    // --- lowestCommonAncestor ---

    public function testLowestCommonAncestorOfValuesInDifferentSubtrees(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(30, $bst->lowestCommonAncestor(20, 40)->getValue());
        $this->assertSame(50, $bst->lowestCommonAncestor(20, 80)->getValue());
    }

    public function testLowestCommonAncestorWhenOneValueIsAncestorOfTheOther(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertSame(50, $bst->lowestCommonAncestor(50, 80)->getValue());
    }

    public function testLowestCommonAncestorReturnsNullWhenAValueIsMissing(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertNull($bst->lowestCommonAncestor(20, 999));
    }

    public function testLowestCommonAncestorOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinarySearchTree())->lowestCommonAncestor(1, 2));
    }

    // --- remove ---

    public function testRemoveLeafNode(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20]);

        $bst->remove(20);

        $this->assertSame([30, 50, 70], $bst->inOrder());
        $this->assertSame(3, $bst->count());
    }

    public function testRemoveNodeWithOnlyRightChild(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 80]);

        $bst->remove(70);

        $this->assertSame([30, 50, 80], $bst->inOrder());
        $this->assertSame(3, $bst->count());
    }

    public function testRemoveNodeWithOnlyLeftChild(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 60]);

        $bst->remove(70);

        $this->assertSame([30, 50, 60], $bst->inOrder());
        $this->assertSame(3, $bst->count());
    }

    public function testRemoveNodeWithTwoChildrenPromotesInOrderSuccessor(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 60, 80]);

        $bst->remove(70);

        $this->assertSame([30, 50, 60, 80], $bst->inOrder());
        $this->assertSame(4, $bst->count());
    }

    public function testRemoveRootOfEntireTree(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $bst->remove(50);

        $this->assertSame([20, 30, 40, 60, 70, 80], $bst->inOrder());
        $this->assertSame(6, $bst->count());
    }

    public function testRemoveOnlyNodeEmptiesTheTree(): void
    {
        $bst = new BinarySearchTree([5]);

        $bst->remove(5);

        $this->assertTrue($bst->isEmpty());
        $this->assertNull($bst->getRoot());
        $this->assertSame(0, $bst->count());
    }

    public function testRemoveNonExistentValueLeavesTreeUnchanged(): void
    {
        $bst = new BinarySearchTree([50, 30, 70]);

        $bst->remove(999);

        $this->assertSame([30, 50, 70], $bst->inOrder());
        $this->assertSame(3, $bst->count());
    }

    public function testRemoveOnEmptyTreeIsANoOp(): void
    {
        $bst = new BinarySearchTree();

        $bst->remove(5);

        $this->assertTrue($bst->isEmpty());
    }

    public function testRemoveReturnsSameInstanceForChaining(): void
    {
        $bst = new BinarySearchTree([50, 30, 70]);

        $result = $bst->remove(30);

        $this->assertSame($bst, $result);
    }

    // --- isValid ---

    public function testIsValidReturnsTrueForEmptyTree(): void
    {
        $this->assertTrue((new BinarySearchTree())->isValid());
    }

    public function testIsValidReturnsTrueForAValidBstWithMultipleNodes(): void
    {
        $bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);

        $this->assertTrue($bst->isValid());
    }

    public function testIsValidReturnsFalseWhenBstOrderIsViolated(): void
    {
        // insert() always maintains BST order, so a corrupted tree has to be
        // built by mutating nodes directly.
        $bst = new BinarySearchTree([50, 30, 70]);
        $bst->getRoot()->getLeft()->setValue(999);

        $this->assertFalse($bst->isValid());
    }

    // --- balance ---

    public function testBalanceOnEmptyTreeIsANoOp(): void
    {
        $bst = new BinarySearchTree();

        $bst->balance();

        $this->assertTrue($bst->isEmpty());
    }

    public function testBalanceOnSingleNodeTreeIsANoOp(): void
    {
        $bst = new BinarySearchTree([5]);

        $bst->balance();

        $this->assertSame(0, $bst->getHeight());
        $this->assertSame([5], $bst->inOrder());
    }

    public function testBalancePreservesAllValuesInSortedOrder(): void
    {
        $bst = new BinarySearchTree([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $bst->balance();

        $this->assertSame(range(1, 10), $bst->inOrder());
        $this->assertSame(10, $bst->count());
    }

    public function testBalanceReducesHeightOfASkewedTree(): void
    {
        // Strictly increasing inserts degrade into a 15-node linked list
        // (height 14); DSW-balancing it should bring it down to the height
        // of a complete binary tree with 15 nodes (height 3).
        $bst = new BinarySearchTree(range(1, 15));
        $this->assertSame(14, $bst->getHeight());

        $bst->balance();

        $this->assertSame(3, $bst->getHeight());
        $this->assertSame(range(1, 15), $bst->inOrder());
    }

    public function testBalanceReturnsSameInstanceForChaining(): void
    {
        $bst = new BinarySearchTree([1, 2, 3]);

        $result = $bst->balance();

        $this->assertSame($bst, $result);
    }
}
