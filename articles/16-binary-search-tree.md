# BinaryTree & BinarySearchTree: a mutable tree hierarchy, plus three real bugs found writing its tests

`Zack\PhpDsAlgo\DataStructure\Tree\BinaryTree` and `...\BinarySearchTree`
are two separate, concrete classes sharing one base (`AbstractTree`) and one
node type (`BinaryTreeNode`). Unlike the linked lists, they're **mutable**
— `insert()`/`remove()`/`balance()` change nodes in place and return `$this`
for chaining, matching `ArrayStack`/`Queue`/`Graph`/the heaps rather than
the clone-then-splice persistent pattern `SingleLinkedList`/
`DoublyLinkedList`/`CircularLinkedList` use (see `articles/00-overview.md`
§1).

## Shape

- `BinaryTreeNode` — a plain box: `value` (mixed) + `left`/`right`
  (`?self`), with getters/setters. No `previous`/parent pointer — every
  traversal in this hierarchy walks top-down from `$root`.
- `AbstractTree` — protected `?BinaryTreeNode $root` and `int $size`, plus
  everything both concrete trees share:

  ```php
  $tree->getRoot();     // ?BinaryTreeNode
  $tree->isEmpty();     // bool, backed by $size === 0 — not a $root null-check
  $tree->clear();       // void, resets root/size
  $tree->getHeight();   // int — -1 empty, 0 a leaf, else 1 + max(childHeights)
  $tree->contains(5);   // bool, breadth-first via a Queue
  $tree->levelOrder();  // list<T>, breadth-first — toArray() is just an alias
  $tree->count();       // int, also via Countable
  foreach ($tree as $v) // in-order (left, node, right) — a private generator, not levelOrder()
  ```

  `getIterator()` and `levelOrder()`/`toArray()` visit nodes in genuinely
  different orders on any tree with more than one level — don't assume
  `foreach` and `toArray()` agree.

  `AbstractTree` also carries a commented-out constructor
  (`buildNodes()`/`connectNodes()`, plus `getLeftChildIndex()`/
  `getRightChildIndex()`/`getParentIndex()` array-index helpers for a
  complete-binary-tree layout) — dead code with no live caller right now,
  not part of either concrete tree's actual construction path. `insert()`
  is how both trees actually get built.

- `BinaryTree` and `BinarySearchTree` both extend `AbstractTree` and add
  their own `insert()`/`remove()`/`search()` with completely different
  strategies (breadth-first vs. BST-order descent — see below).

## `BinaryTree` — plain binary tree, level-order insertion

`insert($value)` fills the first free slot in breadth-first order — a
`Queue` (see `articles/04-queue.md`) walks the tree level by level, and the
first node missing a left or right child gets the new value:

```php
public function insert(mixed $value): static
{
    $newNode = new BinaryTreeNode($value);
    if (is_null($this->root)) {
        $this->root = $newNode;
        $this->size++;
        return $this;
    }
    $queue = new Queue();
    $queue->enqueue($this->root);
    while (!$queue->isEmpty()) {
        $node = $queue->dequeue();
        if (is_null($node->getLeft())) {
            $node->setLeft($newNode);
            $this->size++;
            return $this;
        } else {
            $queue->enqueue($node->getLeft());
        }
        if (is_null($node->getRight())) {
            $node->setRight($newNode);
            $this->size++;
            return $this;
        } else {
            $queue->enqueue($node->getRight());
        }
    }
    return $this;
}
```

This means `BinaryTree` allows duplicate values (there's no ordering
property to check against) and the tree's shape is entirely a function of
insertion order, not of the values themselves — the same values inserted in
a different order can (and usually will) produce a different tree.

`search($value)` and `remove($value)` both scan breadth-first for the same
reason — no ordering to exploit. `remove()` follows the standard
"promote the deepest node" scheme: find the target and, in the same
breadth-first pass, the deepest (last-dequeued) node; if they're the same
node, just detach it; otherwise copy the deepest node's value onto the
target and detach the deepest node instead. `isFull()`/`isComplete()`/
`isPerfect()` check well-known shape properties (every node has 0 or 2
children; every level full except possibly the last, filled left to right;
every internal node has 2 children and every leaf at the same depth,
equivalently `size == 2^(height+1) - 1`).

### Two real bugs, found and fixed while writing this class's test suite

Neither of these showed up until a test asserted the *correct* answer for
a case the existing code happened never to exercise — worth knowing the
shape of both, since the pattern ("two concepts sharing one sentinel value,
or an off-by-one in a combined formula") is exactly the kind of thing that
survives until someone writes the boundary-case test.

**`isBalanced()` always returned `false`.** The height-computing helper
behind it, `checkBalance()`, originally used the same value, `-1`, to mean
two different things: "this child position is empty" (the recursion's base
case) *and* "an imbalance was found somewhere downstream" (the signal that
should short-circuit every caller straight back to `isBalanced()`). A
leaf's two empty children both hit the base case and return `-1` for the
entirely mundane reason that they're empty — but the very next check,
`if ($leftHeight === -1 || $rightHeight === -1) return -1;`, couldn't tell
that apart from a genuine detected imbalance, so it re-signaled `-1` as if
one had been found. That false signal then propagated the same way at
every ancestor, all the way to the root — so `isBalanced()` reported
`false` for *every* non-empty tree, balanced or not, and even for an empty
one (`checkBalance(null)` hits the same `-1` base case directly). The fix
separates the two meanings by giving them different values: an empty
subtree's height is `0` (not `-1`), which frees `-1` to mean *only*
"imbalance found," so it can never again be confused with a real height on
its way up:

```php
private function checkBalance(?BinaryTreeNode $node): int
{
    if (is_null($node)) {
        return 0;                 // empty subtree height is 0, not -1
    }
    $leftHeight = $this->checkBalance($node->getLeft());
    if ($leftHeight === -1) {
        return -1;                // propagate an imbalance found below
    }
    $rightHeight = $this->checkBalance($node->getRight());
    if ($rightHeight === -1) {
        return -1;
    }
    if (abs($leftHeight - $rightHeight) > 1) {
        return -1;                // imbalance found at this node
    }
    return 1 + max($leftHeight, $rightHeight);
}
```

`isBalanced()` itself needed no change — `checkBalance($this->root) !== -1`
already assumed exactly this "−1 means imbalance, nothing else does"
convention; the bug was purely in `checkBalance()` not honoring it.

**`getDiameter()` was off by one edge.** `calculateHeight()` combines a
node's left/right subtree heights into `$currentDiameter = $leftHeight +
$rightHeight + 1` — but under the height convention this class shares with
`AbstractTree::getNodeHeight()` (empty subtree `-1`, leaf `0`, otherwise
`1 + max(...)`), reaching a leaf on the left from the current node costs
`leftHeight + 1` edges, and `rightHeight + 1` on the right, so the full
path through the node is `leftHeight + rightHeight + 2` edges — one more
than the code computed. A tree with exactly one edge (a root and a single
child) has a true diameter of `1`; the buggy version reported `0`. Fixing
*only* that `+1` → `+2` is the whole fix — the height-return line
(`return 1 + max($leftHeight, $rightHeight);`) was already correct and
didn't need touching:

```php
private function calculateHeight(?BinaryTreeNode $node): int
{
    if (is_null($node)) {
        return -1;
    }
    $leftHeight = $this->calculateHeight($node->getLeft());
    $rightHeight = $this->calculateHeight($node->getRight());
    $currentDiameter = $leftHeight + $rightHeight + 2; // was + 1
    $this->maxDiameter = max($this->maxDiameter, $currentDiameter);
    return 1 + max($leftHeight, $rightHeight);
}
```

(An intermediate attempt at this fix changed the *height* return statement
to `2 + max(...)` instead of touching `$currentDiameter` — that inflates
every node's height by one, which then compounds with tree depth: a
7-node perfect tree, true diameter `4`, came back as `7` under that
version. The two quantities — "how do I compute a node's height" and "how
do I turn two child heights into a diameter" — need to stay independently
correct against the same convention; fixing the wrong one of the two can
look right on a shallow tree and still be wrong in general.)

## `BinarySearchTree` — ordered insertion, `O(log n)` average lookups

`insert()`/`search()`/`remove()` all descend by comparing against the
current node's value instead of scanning breadth-first — `O(log n)`
average, `O(n)` worst case (a tree built from already-sorted input
degrades into a linked list; see `balance()` below). Duplicate values are
silently ignored by `insert()` (the else-branch when `$value` equals the
current node's value just returns `$this` unchanged) — unlike `BinaryTree`,
which allows duplicates freely.

`remove($value)` has the standard three cases once the target node is
found: a leaf is just detached; a node with exactly one child is replaced
by that child; a node with two children has its **value overwritten** by
its in-order successor's value (the minimum of its right subtree), and
that successor node is detached instead. The node object itself never
moves in the two-children case — only the value inside it changes — so
holding a `BinaryTreeNode` reference from before a `remove()` call and
expecting its value to still mean what it meant then isn't safe.

### The rest of the ordered API

```php
$bst->min();  $bst->max();                    // ?BinaryTreeNode
$bst->predecessor($v);  $bst->successor($v);  // ?BinaryTreeNode — largest < / smallest >, whether or not $v itself exists
$bst->floor($v);  $bst->ceiling($v);           // T|null — largest <= / smallest >=
$bst->findClosest($v);                          // T — nearest value; ties favor the shallower node in the search path
$bst->rangeSearch($lo, $hi);  $bst->countInRange($lo, $hi); // list<T> / int, inclusive, O(n) worst case but prunes whole subtrees outside range
$bst->kthSmallest($k);  $bst->kthLargest($k);   // T|null — null if $k is out of bounds (0, negative, or > size)
$bst->lowestCommonAncestor($a, $b);              // ?BinaryTreeNode — null if either value is absent
$bst->inOrder();                                  // list<T>, ascending — BST in-order traversal is sorted "for free"
```

`predecessor()`/`successor()` work for a value that isn't actually in the
tree too — they track the best candidate seen while descending and fall
back to it once the descent runs off the tree. `findClosest()` returns the
exact value immediately on a match; otherwise it tracks the closest node
seen by absolute distance, and because the comparison is strict `<` (not
`<=`), an exact tie in distance keeps whichever candidate was found first
during the root-to-leaf descent — which is always the shallower of the two,
since the search always visits an ancestor before either of its
descendants.

### `isValid()` — the third bug, a crash rather than a wrong answer

`isValid()` checks the BST invariant (every node's value strictly between
its left and right subtrees' values) via an in-order walk, delegating to a
private `inOrderCheck()`. That helper's signature was
`inOrderCheck(BinaryTreeNode $node, ...)` — **missing the `?`** that every
other recursive tree-walking helper in this class has on its node
parameter. Its very first line, `if ($node === null) return true;`, looks
like a base case, but it's dead code: PHP rejects `null` for a non-nullable
class-typed parameter *before* the function body ever runs. `isValid()`
itself is guarded (`if ($this->root === null) return true;`), so the
*first* call into `inOrderCheck()` is always safe — but that function
immediately recurses into `$node->getLeft()`/`$node->getRight()`, and for
any leaf (or any node missing one side), those are `null`. So the crash
wasn't limited to malformed trees — it happened on *every* non-empty tree,
including a single node (whose two children are both `null`), the instant
the first recursive call fired:

```
TypeError: BinarySearchTree::inOrderCheck(): Argument #1 ($node) must be
of type BinaryTreeNode, null given
```

The fix is exactly the missing character: `inOrderCheck(?BinaryTreeNode
$node, ...)`. Once callable with `null`, the dead-looking base case at the
top becomes real, and the rest of the method — recurse left, check
`previous < current`, update `previous`, recurse right — works as
written.

## `balance()` — a genuine Day-Stout-Warren rebalance

```php
public function balance(): static
{
    if (is_null($this->root) || $this->size <= 1) {
        return $this;
    }
    $this->createVine();
    $this->compressVine();
    return $this;
}
```

This is DSW, not an AVL rotation — no per-node balance factors, no
rebalancing on every insert; it's a one-shot, two-pass O(n) operation you
call when you want the *current* tree flattened into an optimally-shaped
one.

**Pass 1 — `createVine()`** rotates the whole tree into a "vine": a
right-only chain with no left children anywhere, via repeated right
rotations, without changing the in-order value sequence at all. A dummy
node holds the real root as its right child so the very first rotation (if
the root itself needs one) has somewhere to attach to; walking down the
resulting right-chain, any node whose right child still has a *left* child
gets that left child rotated up.

**Pass 2 — `compressVine()`** repeatedly left-rotates that vine back down
into a balanced shape. `$m` is the largest `2^k - 1` not exceeding the
tree's size — the node count of the largest perfect binary tree that fits.
The first compression pass (`$n - $m` rotations) trims the vine down to
exactly `$m` nodes' worth of extra length; each subsequent pass halves `$m`
and compresses again, mirroring how a perfect tree's level sizes halve
going up, until the vine has fully folded into the balanced tree. Each
individual `compress($dummy, $count)` call performs `$count` left
rotations along the vine, each one pulling the node two steps down
(`$current->getRight()->getRight()`) up to replace `$current`'s right
child, and pushing the old right child down to become that promoted node's
new left child.

A tree built by inserting values in strictly increasing order degrades
into an `n`-node chain (height `n - 1` — the BST-worst-case scenario
mentioned above); calling `balance()` on it brings the height down to that
of a complete binary tree with the same node count (e.g. 15 nodes:
height 14 → height 3).

## Complexity summary

| Operation | `BinaryTree` | `BinarySearchTree` | Notes |
|---|---|---|---|
| `insert` | O(n) | O(log n) average, O(n) worst | `BinaryTree`: breadth-first scan to the first free slot; `BinarySearchTree`: BST-order descent |
| `search` / `contains` | O(n) | O(log n) average, O(n) worst | `BinaryTree::search()` is breadth-first; `contains()` (inherited from `AbstractTree`) always is, on either tree |
| `remove` | O(n) | O(log n) average, O(n) worst | |
| `min` / `max` (`BinarySearchTree`) | — | O(n) as implemented | Breadth-first scan over every node comparing values — despite the O(log n) docblock, it doesn't exploit BST order the way `predecessor`/`successor`/`floor`/`ceiling` do |
| `predecessor` / `successor` / `floor` / `ceiling` / `findClosest` | — | O(log n) average, O(n) worst | Single root-to-leaf descent |
| `rangeSearch` / `countInRange` | — | O(n) worst, prunes out-of-range subtrees | O(m) extra space for `rangeSearch`'s result, m = values in range |
| `kthSmallest` / `kthLargest` | — | O(k) | Short-circuits once the count reaches `$k`; O(n) worst case if `$k` is near `size` |
| `lowestCommonAncestor` | — | O(log n) average, O(n) worst | Two `search()` calls up front (existence check) plus one descent |
| `isValid` | — | O(n) | Single in-order walk |
| `balance` | — | O(n) | Two linear passes (`createVine`/`compressVine`) |
| `isFull` / `isComplete` / `isPerfect` / `isBalanced` / `getDiameter` (`BinaryTree`) | O(n) | — | Each a single full traversal |
| `getHeight` / `levelOrder` / `toArray` / `contains` (inherited) | O(n) | O(n) | Shared via `AbstractTree` |

`min()`'s O(n) behavior is worth calling out specifically: its docblock
claims "O(log n) average, O(n) worst case," matching the BST-descent
approach `predecessor()`/`successor()`/`floor()`/`ceiling()` actually use,
but the implementation itself does a full breadth-first scan comparing
every node's value instead of walking left from the root. It returns the
correct answer either way — just not in the time the docblock promises.
