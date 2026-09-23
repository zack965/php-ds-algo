# DoublyLinkedList

**Namespace:** `Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly`
**Classes:** `DoublyLinkedList`, `DoublyLinkedListNode`
**Contract:** `Zack\PhpDsAlgo\Contracts\IDoublyLinkedList`

## What it is

A **doubly linked list** is a chain of nodes where each node points to both
its **next** and its **previous** neighbor. You can walk the list in either
direction from any node.

`DoublyLinkedList` has the same method surface as `SingleLinkedList`, and
the shared contracts keep the two in step. Like its singly linked
counterpart, it is **persistent**: every operation that changes the list
returns a new list and leaves the original unchanged.

```php
use Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedList;

$list = DoublyLinkedList::of(['a', 'b', 'c']);
$next = $list->insert('x', 1);

$list->toArrayValues(); // ['a', 'b', 'c']
$next->toArrayValues(); // ['a', 'x', 'b', 'c']

$tail = $next->getTail();
$tail->getPrevious()->getValue(); // 'b'
```

## How it works

- `DoublyLinkedListNode` holds a `value`, a `next` pointer and a `previous`
  pointer. It prints as `Node(value=..., previous=..., next=...)`, so you can
  see both neighbors at once while debugging.
- Every insertion and removal updates the links on **both sides** of the
  change, so `next` and `previous` always agree.
- Operations that change the list work on a fresh copy of the chain, so
  earlier versions of the list keep all their `next` and `previous` links.

## Creating a list

```php
DoublyLinkedList::empty();
DoublyLinkedList::of([1, 2, 3]);
DoublyLinkedList::fromIterable([1, 2, 3]); // array-like iterable with sequential keys
DoublyLinkedList::fromNodes([$n1, $n2]);
DoublyLinkedList::ofObjects([$n1, $n2]);
```

## API

The API matches [`SingleLinkedList`](01-single-linked-list.md):

- **Insertion:** `prepend`, `append`, `insert`, `insertBeforeNode`,
  `insertAfterNode`
- **Removal:** `removeHead`, `removeTail`, `removeAt`, `removeByValue`,
  `clear`, `clearAndKeepHead`
- **Access:** `getHead`, `getTail`, `get`, `contains`, `indexOf`, `getLength`
- **Transformation:** `reverse`, `map`, `filter`, `reduce`, `toArray`,
  `toArrayValues`
- **Iteration:** `foreach` over nodes (`IteratorAggregate`)

Each node also has `getPrevious()` / `setPrevious()`, which let you walk
backward:

```php
$node = $list->getTail();
while ($node !== null) {
    echo $node->getValue();
    $node = $node->getPrevious();
}
```

Errors use the same `ErrorMessages` constants and `InvalidArgumentException`
as the singly linked list.

## Complexity

| Operation | Time |
|---|---|
| `prepend` / `append` / `insert` | O(n) |
| `removeHead` / `removeTail` / `removeAt` / `removeByValue` | O(n) |
| `get` / `indexOf` / `contains` / `getTail` | O(n) |
| `reverse` | O(n) |
| `map` / `filter` / `reduce` | O(n) |
| Moving to a neighbor from a node you hold (`getNext` / `getPrevious`) | O(1) |

Space: O(n). Each node stores one more pointer than a singly linked node.

## When to use it

- You need to **move in both directions**: browser-style back/forward
  history, text-editor cursors, or media playlists with "previous" and
  "next".
- You hold a node and want its **predecessor** without walking from the
  head.
- You want the persistence guarantees of the linked lists (safe sharing,
  snapshots, functional transformations) together with backward navigation.

## When to choose something else

- **Forward-only traversal with frequent prepends:** `SingleLinkedList`
  prepends in O(1) and uses less memory per node.
- **Index-based access:** a plain PHP array.
- **Push/pop at both ends as a work queue:** `Deque` is designed for that
  access pattern.
