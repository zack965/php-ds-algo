<?php

namespace Tests\Unit\DataStructure\Tree;

use PHPUnit\Framework\TestCase;
use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTreeNode;

class BinaryTreeNodeTest extends TestCase
{
    public function testGetValueReturnsConstructedValue(): void
    {
        $node = new BinaryTreeNode(5);

        $this->assertSame(5, $node->getValue());
    }

    public function testAcceptsArbitraryMixedValue(): void
    {
        $payload = ['id' => 1, 'name' => 'alpha'];
        $node = new BinaryTreeNode($payload);

        $this->assertSame($payload, $node->getValue());
    }

    public function testAcceptsNullValue(): void
    {
        $node = new BinaryTreeNode(null);

        $this->assertNull($node->getValue());
    }

    public function testSetValueReplacesStoredValue(): void
    {
        $node = new BinaryTreeNode(5);

        $node->setValue(10);

        $this->assertSame(10, $node->getValue());
    }

    public function testNewNodeHasNoChildren(): void
    {
        $node = new BinaryTreeNode(5);

        $this->assertNull($node->getLeft());
        $this->assertNull($node->getRight());
    }

    public function testGetLeftReturnsSetLeftChild(): void
    {
        $node = new BinaryTreeNode(5);
        $left = new BinaryTreeNode(3);

        $node->setLeft($left);

        $this->assertSame($left, $node->getLeft());
    }

    public function testGetRightReturnsSetRightChild(): void
    {
        $node = new BinaryTreeNode(5);
        $right = new BinaryTreeNode(8);

        $node->setRight($right);

        $this->assertSame($right, $node->getRight());
    }

    public function testSetLeftToNullDetachesLeftChild(): void
    {
        $node = new BinaryTreeNode(5);
        $node->setLeft(new BinaryTreeNode(3));

        $node->setLeft(null);

        $this->assertNull($node->getLeft());
    }

    public function testSetRightToNullDetachesRightChild(): void
    {
        $node = new BinaryTreeNode(5);
        $node->setRight(new BinaryTreeNode(8));

        $node->setRight(null);

        $this->assertNull($node->getRight());
    }

    public function testSettingLeftDoesNotAffectRight(): void
    {
        $node = new BinaryTreeNode(5);
        $right = new BinaryTreeNode(8);
        $node->setRight($right);

        $node->setLeft(new BinaryTreeNode(3));

        $this->assertSame($right, $node->getRight());
    }
}
