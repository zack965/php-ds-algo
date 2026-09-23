# BinaryTree & BinarySearchTree

**Namespace:** `Zack\PhpDsAlgo\DataStructure\Tree`
**Classes:** `BinaryTree`, `BinarySearchTree`, `AbstractTree`, `BinaryTreeNode`
**Contracts:** `ITree`, `IBinaryTree`, `IBinarySearchTree`

## What it is

A **binary tree** is a hierarchy where each node has at most two children,
**left** and **right**. The top node is the **root**, and nodes with no
children are **leaves**.

The library provides two binary trees built on a shared base:

- **`BinaryTree`** fills the tree **level by level**, left to right, in the
  order you insert values. Its shape depends only on insertion order, and it
  can hold duplicate values.
- **`BinarySearchTree`** (BST) keeps values **ordered**. Everything in a
  node's left subtree is smaller than the node and everything in its right
  subtree is larger. This makes searches, ordered queries and range lookups
  fast. Duplicate values are stored once.

Both are **mutable**, and `insert()`, `remove()` and `balance()` return the
tree, so calls can be chained.

## Shared features (`AbstractTree`)

```php
$tree->getRoot();     // ?BinaryTreeNode (getValue(), getLeft(), getRight())
$tree->isEmpty();
$tree->count();       // also count($tree)
$tree->clear();
$tree->getHeight();   // -1 for an empty tree, 0 for a single node
$tree->contains($v);
$tree->levelOrder();  // breadth-first, level by level
$tree->toArray();     // same as levelOrder()
foreach ($tree as $v) // in-order: left, node, right
```

`foreach` walks the tree **in order**. `levelOrder()` and `toArray()` walk it
**level by level**. Pick whichever order your task needs.

## BinaryTree

```php
use Zack\PhpDsAlgo\DataStructure\Tree\BinaryTree;

$tree = new BinaryTree();
foreach ([1, 2, 3, 4, 5] as $v) {
    $tree->insert($v);
}
//         1
//       /   \
//      2     3
//     / \
//    4   5

$tree->levelOrder(); // [1, 2, 3, 4, 5]
$tree->preOrder();   // [1, 2, 4, 5, 3]
$tree->inOrder();    // [4, 2, 5, 1, 3]
$tree->postOrder();  // [4, 5, 2, 3, 1]
```

### API

```php
$tree->insert($value);   // fills the first free slot, level by level
$tree->remove($value);   // replaces the node with the deepest node, keeping the tree compact
$tree->search($value);   // ?BinaryTreeNode

$tree->preOrder();       // node, left, right
$tree->inOrder();        // left, node, right
$tree->postOrder();      // left, right, node

$tree->isFull();         // every node has 0 or 2 children
$tree->isComplete();     // every level full except possibly the last, filled left to right
$tree->isPerfect();      // all internal nodes have 2 children and all leaves share a depth
$tree->isBalanced();     // subtree heights differ by at most 1 everywhere
$tree->getDiameter();    // longest path between any two nodes, in edges
```

For the tree above: `isComplete()` → `true`, `isFull()` → `true`,
`isPerfect()` → `false`, `isBalanced()` → `true`, `getDiameter()` → `3`.

Because it fills level by level, a `BinaryTree` stays **complete** as you
insert, which keeps it as shallow as possible.

## BinarySearchTree

```php
use Zack\PhpDsAlgo\DataStructure\Tree\BinarySearchTree;

$bst = new BinarySearchTree([50, 30, 70, 20, 40, 60, 80]);
// or: BinarySearchTree::fromArray([...])
//          50
//        /    \
//      30      70
//     /  \    /  \
//    20  40  60  80

$bst->inOrder();               // [20, 30, 40, 50, 60, 70, 80] (always sorted)
$bst->search(60);              // node holding 60
$bst->floor(45);               // 40
$bst->ceiling(45);             // 50
$bst->rangeSearch(25, 65);     // [30, 40, 50, 60]
$bst->kthSmallest(2);          // 30
$bst->lowestCommonAncestor(20, 40)->getValue(); // 30
```

### API

**Core**
```php
$bst->insert($value);   // duplicates are ignored
$bst->remove($value);
$bst->search($value);   // ?BinaryTreeNode
$bst->isValid();        // checks the BST ordering rule
$bst->inOrder();        // sorted values
```

**Ordered queries**
```php
$bst->min();                  // ?BinaryTreeNode
$bst->max();                  // ?BinaryTreeNode
$bst->predecessor($v);        // largest value < $v  (?BinaryTreeNode)
$bst->successor($v);          // smallest value > $v (?BinaryTreeNode)
$bst->floor($v);              // largest value <= $v, or null
$bst->ceiling($v);            // smallest value >= $v, or null
$bst->findClosest($v);        // value nearest to $v
$bst->kthSmallest($k);        // k-th smallest (1-based), or null
$bst->kthLargest($k);         // k-th largest (1-based), or null
$bst->rangeSearch($lo, $hi);  // values in [lo, hi], sorted
$bst->countInRange($lo, $hi); // number of values in [lo, hi]
$bst->lowestCommonAncestor($a, $b); // ?BinaryTreeNode
```

`predecessor`, `successor`, `floor`, `ceiling` and `findClosest` also work
for values that are **not** in the tree, which makes them good for
"nearest match" lookups.

**Rebalancing**
```php
$bst->balance();
```

`balance()` uses the **Day-Stout-Warren** algorithm. In two linear passes it
reshapes the tree into its most compact form, with the same values and the
same order. For example, a 15-node tree built from sorted input goes from
height 14 to height 3:

```php
$bst = new BinarySearchTree(range(1, 15));
$bst->getHeight(); // 14
$bst->balance();
$bst->getHeight(); // 3
```

Call it after bulk-loading data, or whenever you want lookups to be as fast
as possible.

## Complexity

h = height of the tree: about log n when the tree is balanced, up to n
otherwise.

| Operation | BinaryTree | BinarySearchTree |
|---|---|---|
| `insert` | O(n) | O(h) |
| `search` | O(n) | O(h) |
| `remove` | O(n) | O(h) |
| `contains` (shared) | O(n) | O(n) |
| `predecessor` / `successor` / `floor` / `ceiling` / `findClosest` | — | O(h) |
| `lowestCommonAncestor` | — | O(h) |
| `min` / `max` | — | O(n) |
| `kthSmallest` / `kthLargest` | — | O(h + k) |
| `rangeSearch` / `countInRange` | — | O(h + number of matches), skips subtrees outside the range |
| `inOrder` / `levelOrder` / `preOrder` / `postOrder` | O(n) | O(n) |
| `isValid` / `isFull` / `isComplete` / `isPerfect` / `isBalanced` / `getDiameter` | O(n) | O(n) |
| `balance` | — | O(n) |

Space: O(n) for the nodes, plus O(h) for recursive walks.

## When to use BinaryTree

- **Modeling hierarchies** where position matters more than order: decision
  trees, expression trees, tournament brackets.
- **Learning and teaching** tree traversals and tree properties (full,
  complete, perfect, balanced, diameter).
- **Complete-tree layouts,** the same shape a binary heap uses.
- **Checking the structure** of a tree with the built-in shape predicates.

## When to use BinarySearchTree

- **Sorted data that changes over time:** a leaderboard, an order book, a
  schedule, where you insert and remove values and still read them back in
  order.
- **Nearest-value lookups:** the closest price, the next available time slot,
  the previous version number (`floor`, `ceiling`, `predecessor`,
  `successor`, `findClosest`).
- **Range queries:** "every event between 10:00 and 12:00"
  (`rangeSearch`, `countInRange`).
- **Rank queries:** the k-th smallest or largest value, medians and
  percentiles.
- **Unique values kept sorted** without re-sorting after each change.

## When to choose something else

- **Only key lookups, with no ordering needed:** `HashMap` or `HashTable`
  give O(1) average lookups.
- **Only ever taking the smallest or largest item:** `MinHeap`, `MaxHeap` or
  `PriorityQueue`.
- **Arbitrary relationships** (many parents, cycles): `Graph`.
- **A plain sequence without ordering rules:** a linked list, `Queue` or a
  PHP array.
