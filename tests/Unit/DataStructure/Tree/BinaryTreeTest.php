<?php

namespace Tests\Unit\DataStructure\Tree;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTree;
use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTreeNode;

class BinaryTreeTest extends TestCase
{
    /**
     * insert() fills the tree breadth-first (level order), so building from
     * a plain value list gives a predictable, easy-to-reason-about shape.
     */
    private function buildLevelOrder(array $values): BinaryTree
    {
        $tree = new BinaryTree();
        foreach ($values as $value) {
            $tree->insert($value);
        }
        return $tree;
    }

    // --- Inherited AbstractTree behaviour (smoke tests; fuller coverage
    // lives in BinarySearchTreeTest since AbstractTree is shared) ---

    public function testNewTreeIsEmpty(): void
    {
        $tree = new BinaryTree();

        $this->assertTrue($tree->isEmpty());
        $this->assertNull($tree->getRoot());
        $this->assertSame(0, $tree->count());
    }

    public function testClearResetsTheTree(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3]);

        $tree->clear();

        $this->assertTrue($tree->isEmpty());
        $this->assertNull($tree->getRoot());
    }

    public function testContainsFindsAnInsertedValue(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4]);

        $this->assertTrue($tree->contains(4));
        $this->assertFalse($tree->contains(99));
    }

    public function testLevelOrderReflectsBreadthFirstInsertOrder(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5]);

        $this->assertSame([1, 2, 3, 4, 5], $tree->levelOrder());
        $this->assertSame($tree->levelOrder(), $tree->toArray());
    }

    public function testGetIteratorYieldsValuesInOrder(): void
    {
        // getIterator() is AbstractTree's own left-root-right traversal,
        // independent of insertion order.
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5]);

        $values = [];
        foreach ($tree as $value) {
            $values[] = $value;
        }

        $this->assertSame($tree->inOrder(), $values);
    }

    public function testGetHeightOfLevelOrderFilledTree(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $this->assertSame(2, $tree->getHeight());
    }

    // --- Traversals: preOrder / inOrder / postOrder ---

    public function testPreOrderVisitsRootThenLeftThenRight(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $this->assertSame([1, 2, 4, 5, 3, 6, 7], $tree->preOrder());
    }

    public function testInOrderVisitsLeftThenRootThenRight(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $this->assertSame([4, 2, 5, 1, 6, 3, 7], $tree->inOrder());
    }

    public function testPostOrderVisitsLeftThenRightThenRoot(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $this->assertSame([4, 5, 2, 6, 7, 3, 1], $tree->postOrder());
    }

    public function testTraversalsOnEmptyTreeReturnEmptyArrays(): void
    {
        $tree = new BinaryTree();

        $this->assertSame([], $tree->preOrder());
        $this->assertSame([], $tree->inOrder());
        $this->assertSame([], $tree->postOrder());
    }

    // --- insert ---

    public function testInsertFillsLevelsBreadthFirstLeftToRight(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4]);

        $root = $tree->getRoot();
        $this->assertSame(1, $root->getValue());
        $this->assertSame(2, $root->getLeft()->getValue());
        $this->assertSame(3, $root->getRight()->getValue());
        $this->assertSame(4, $root->getLeft()->getLeft()->getValue());
    }

    public function testInsertReturnsSameInstanceForChaining(): void
    {
        $tree = new BinaryTree();

        $result = $tree->insert(1)->insert(2)->insert(3);

        $this->assertSame($tree, $result);
        $this->assertSame(3, $tree->count());
    }

    public function testInsertAllowsDuplicateValues(): void
    {
        // Unlike BinarySearchTree, the plain BinaryTree has no ordering
        // property to enforce uniqueness against.
        $tree = $this->buildLevelOrder([1, 1, 1]);

        $this->assertSame(3, $tree->count());
    }

    // --- search ---

    public function testSearchFindsAnExistingValue(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4]);

        $node = $tree->search(4);

        $this->assertInstanceOf(BinaryTreeNode::class, $node);
        $this->assertSame(4, $node->getValue());
    }

    public function testSearchReturnsNullForMissingValue(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3]);

        $this->assertNull($tree->search(99));
    }

    public function testSearchOnEmptyTreeReturnsNull(): void
    {
        $this->assertNull((new BinaryTree())->search(1));
    }

    // --- remove ---

    public function testRemoveOnlyNodeEmptiesTheTree(): void
    {
        $tree = $this->buildLevelOrder([1]);

        $tree->remove(1);

        $this->assertTrue($tree->isEmpty());
        $this->assertNull($tree->getRoot());
        $this->assertSame(0, $tree->count());
    }

    public function testRemoveOnSingleNodeTreeWithNonMatchingValueIsANoOp(): void
    {
        $tree = $this->buildLevelOrder([1]);

        $tree->remove(99);

        $this->assertSame(1, $tree->count());
        $this->assertSame([1], $tree->levelOrder());
    }

    public function testRemoveTargetThatIsItselfTheDeepestNode(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $tree->remove(7);

        $this->assertSame(6, $tree->count());
        $this->assertSame([1, 2, 3, 4, 5, 6], $tree->levelOrder());
    }

    public function testRemoveTargetPromotesDeepestNodesValueIntoItsPlace(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $tree->remove(2);

        // Node 2 is replaced with the deepest/last node's value (7), and
        // that now-empty deepest slot is detached.
        $this->assertSame(6, $tree->count());
        $this->assertSame([1, 7, 3, 4, 5, 6], $tree->levelOrder());
    }

    public function testRemoveNonExistentValueLeavesTreeUnchanged(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3]);

        $tree->remove(99);

        $this->assertSame(3, $tree->count());
        $this->assertSame([1, 2, 3], $tree->levelOrder());
    }

    public function testRemoveOnEmptyTreeIsANoOp(): void
    {
        $tree = new BinaryTree();

        $tree->remove(1);

        $this->assertTrue($tree->isEmpty());
    }

    public function testRemoveReturnsSameInstanceForChaining(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3]);

        $result = $tree->remove(2);

        $this->assertSame($tree, $result);
    }

    // --- isFull: every node has exactly 0 or 2 children ---

    public function testIsFullReturnsTrueForAPerfectlyFullTree(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $this->assertTrue($tree->isFull());
    }

    public function testIsFullReturnsFalseWhenANodeHasOnlyOneChild(): void
    {
        // Level-order fill of 4 values leaves node "2" with only a left child.
        $tree = $this->buildLevelOrder([1, 2, 3, 4]);

        $this->assertFalse($tree->isFull());
    }

    public function testIsFullReturnsTrueForEmptyTree(): void
    {
        $this->assertTrue((new BinaryTree())->isFull());
    }

    public function testIsFullReturnsTrueForSingleNodeTree(): void
    {
        $this->assertTrue($this->buildLevelOrder([1])->isFull());
    }

    // --- isComplete: every level full except possibly the last, filled left-to-right ---

    public function testIsCompleteReturnsTrueForAPerfectlyFullTree(): void
    {
        $this->assertTrue($this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7])->isComplete());
    }

    public function testIsCompleteReturnsTrueWhenLastLevelIsPartiallyFilledLeftToRight(): void
    {
        // Level-order insert always fills left-to-right, so this stays complete.
        $this->assertTrue($this->buildLevelOrder([1, 2, 3, 4, 5])->isComplete());
    }

    public function testIsCompleteReturnsFalseWhenALaterNodeHasChildrenPastAGap(): void
    {
        $tree = new BinaryTree();
        $tree->insert(1);
        $root = $tree->getRoot();
        $root->setLeft(new BinaryTreeNode(2));
        $root->setRight(new BinaryTreeNode(3));
        // node 3 gets a right child while node 2 has no children at all,
        // leaving a gap before it in level order.
        $root->getRight()->setRight(new BinaryTreeNode(5));

        $this->assertFalse($tree->isComplete());
    }

    public function testIsCompleteReturnsTrueForEmptyTree(): void
    {
        $this->assertTrue((new BinaryTree())->isComplete());
    }

    // --- isPerfect: every internal node has 2 children and every leaf is at the same depth ---

    public function testIsPerfectReturnsTrueForAPerfectTree(): void
    {
        $this->assertTrue($this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7])->isPerfect());
    }

    public function testIsPerfectReturnsFalseForANonPerfectTree(): void
    {
        $this->assertFalse($this->buildLevelOrder([1, 2, 3, 4])->isPerfect());
    }

    public function testIsPerfectReturnsTrueForSingleNodeTree(): void
    {
        $this->assertTrue($this->buildLevelOrder([1])->isPerfect());
    }

    public function testIsPerfectReturnsTrueForEmptyTree(): void
    {
        $this->assertTrue((new BinaryTree())->isPerfect());
    }

    // --- isBalanced ---

    public function testIsBalancedReturnsTrueForEmptyTree(): void
    {
        $this->assertTrue((new BinaryTree())->isBalanced());
    }

    public function testIsBalancedReturnsTrueForASingleNodeTree(): void
    {
        $this->assertTrue($this->buildLevelOrder([1])->isBalanced());
    }

    public function testIsBalancedReturnsTrueForAPerfectlyBalancedTree(): void
    {
        $this->assertTrue($this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7])->isBalanced());
    }

    public function testIsBalancedReturnsFalseForAGenuinelyUnbalancedTree(): void
    {
        // NOTE: as of this writing, checkBalance() still uses a single
        // sentinel value (0) to mean both "an imbalance was found downstream"
        // and a subtree's legitimate computed height, so a real imbalance
        // can still be missed if it happens to collide with an actual height
        // of 0 along the way. This one-sided 4-deep chain (root -> left ->
        // left -> left, nothing on the right at any level) is unambiguously
        // unbalanced, but isBalanced() currently returns true for it. This
        // test documents the intended/correct behaviour and is expected to
        // fail until that's fixed.
        $tree = new BinaryTree();
        $tree->insert(1);
        $root = $tree->getRoot();
        $left = new BinaryTreeNode(2);
        $root->setLeft($left);
        $leftLeft = new BinaryTreeNode(3);
        $left->setLeft($leftLeft);
        $leftLeftLeft = new BinaryTreeNode(4);
        $leftLeft->setLeft($leftLeftLeft);

        $this->assertFalse($tree->isBalanced());
    }

    // --- getDiameter: length (in edges) of the longest path between any two nodes ---

    public function testGetDiameterOfEmptyTreeIsZero(): void
    {
        $this->assertSame(0, (new BinaryTree())->getDiameter());
    }

    public function testGetDiameterOfSingleNodeTreeIsZero(): void
    {
        $this->assertSame(0, $this->buildLevelOrder([1])->getDiameter());
    }

    public function testGetDiameterOfTwoNodeTreeIsOne(): void
    {
        $tree = $this->buildLevelOrder([1, 2]);

        $this->assertSame(1, $tree->getDiameter());
    }

    public function testGetDiameterOfRootWithTwoLeavesIsTwo(): void
    {
        $tree = $this->buildLevelOrder([1, 2, 3]);

        $this->assertSame(2, $tree->getDiameter());
    }

    public function testGetDiameterOfPerfectSevenNodeTreeIsFour(): void
    {
        // Longest path is leaf -> parent -> root -> parent -> leaf: 4 edges.
        // Exercises a deeper tree, where the earlier (now-fixed) bug used to
        // compound with depth instead of staying a flat off-by-one.
        $tree = $this->buildLevelOrder([1, 2, 3, 4, 5, 6, 7]);

        $this->assertSame(4, $tree->getDiameter());
    }
}
