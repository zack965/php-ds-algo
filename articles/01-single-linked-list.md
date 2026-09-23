# SingleLinkedList & CircularLinkedList

**Namespace:** `Zack\PhpDsAlgo\DataStructure\LinkedList\Single`
**Classes:** `SingleLinkedList`, `CircularLinkedList`, `SingleLinkedListNode`
**Contract:** `Zack\PhpDsAlgo\Contracts\ILinkedList`

## What it is

A **singly linked list** is a chain of nodes. Each node holds a value and a
reference to the next node. The list keeps a reference to the first node, the
**head**, and reaches every other element by following `next` links.

In this library the linked lists are **persistent**. Each operation that
changes the list (`append`, `prepend`, `insert`, `removeAt`, `map`,
`filter`, …) returns a **new** list and leaves the original list
unchanged. You can hold onto any version and it keeps its contents.

```php
use Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList;

$v1 = SingleLinkedList::of([1, 2, 3]);
$v2 = $v1->append(4);
$v3 = $v2->removeHead();

$v1->toArrayValues(); // [1, 2, 3]
$v2->toArrayValues(); // [1, 2, 3, 4]
$v3->toArrayValues(); // [2, 3, 4]
```

## How it works

- `SingleLinkedListNode` holds a `value` and a `next` pointer, and prints as
  `Node(value=1, next=2)` when you debug it.
- `SingleLinkedList` holds the `head` and the list's `length`.
- Operations that change the list copy the node chain, apply the change to
  the copy, and wrap the copy in a new list object.
- `prepend()` and `removeHead()` reuse the existing chain directly, so both
  run in constant time.
- `reverse()` re-links the existing nodes in a single pass, without copying
  them, and returns the reversed list. Continue working with the list it
  returns. If you also need the original order, keep a copy first, for
  example `SingleLinkedList::of($list->toArrayValues())`.

## Creating a list

You create lists through static factories:

```php
SingleLinkedList::empty();                 // empty list
SingleLinkedList::of([1, 2, 3]);           // from raw values
SingleLinkedList::fromIterable($generator); // from any iterable, in one pass
SingleLinkedList::fromNodes([$n1, $n2]);   // link pre-built nodes together
SingleLinkedList::ofObjects([$n1, $n2]);   // alias of fromNodes()
```

## API

### Insertion
```php
$list->prepend($value);                  // add at the front
$list->append($value);                   // add at the end
$list->insert($value, $index);           // add at a position (0..length)
$list->insertBeforeNode($target, $value); // add before the first node holding $target
$list->insertAfterNode($target, $value);  // add after the first node holding $target
```

### Removal
```php
$list->removeHead();
$list->removeTail();
$list->removeAt($index);
$list->removeByValue($value);
$list->clear();             // empty list
$list->clearAndKeepHead();  // one-node list holding the current head's value
```

### Access & search
```php
$list->getHead();        // ?SingleLinkedListNode
$list->getTail();        // SingleLinkedListNode
$list->get($index);      // SingleLinkedListNode at an index
$list->contains($value); // the matching node
$list->indexOf($value);  // position of the value
$list->getLength();
```

### Transformation & functional methods
```php
$list->reverse();
$list->map(fn($v) => $v * 2);
$list->filter(fn($v) => $v % 2 === 0);
$list->reduce(fn($carry, $v) => $carry + $v, 0);
$list->toArray();        // nodes
$list->toArrayValues();  // values
```

### Iteration
`SingleLinkedList` implements `IteratorAggregate`. `foreach` yields
**nodes**, so call `getValue()` on each one, or use `toArrayValues()` to get
plain values.

```php
foreach ($list as $node) {
    echo $node->getValue();
}
```

### Errors
Invalid operations throw `InvalidArgumentException` with a message from
`ErrorMessages`: `LINKEDLIST_IS_EMPTY`, `INDEX_OUT_OF_BOUND` or
`NO_NODE_WITH_THIS_VALUE`. Your code gets a clear error instead of a silent
`null`.

## Complexity

| Operation | Time |
|---|---|
| `prepend` | O(1) |
| `removeHead` | O(1) |
| `append` | O(n) |
| `insert` / `insertBeforeNode` / `insertAfterNode` | O(n) |
| `removeAt` / `removeTail` / `removeByValue` | O(n) |
| `get` / `indexOf` / `contains` / `getTail` | O(n) |
| `reverse` | O(n) |
| `map` / `filter` / `reduce` | O(n) |

Space: O(n) for the list. Each new version that copies the chain uses O(n)
more space.

## CircularLinkedList

`CircularLinkedList` has the same API, factories and persistent behavior as
`SingleLinkedList`, and its `reverse()` also returns a new list and leaves
the original unchanged. The difference is that the **tail links back to the
head**. For any non-empty list:

```php
$list->getTail()->getNext() === $list->getHead(); // true
```

Iteration, `toArray()`, `map()`, `filter()` and the other traversals visit
each element exactly once, so you use them the same way as on a regular list.

Circular lists are a natural fit for **round-robin** and **cyclic** problems.
Examples include rotating turns between players, scheduling tasks in a
repeating cycle, and cycling through playlist items or carousel slides.

## When to use it

- You want an **immutable, shareable sequence**. Each version is safe to pass
  around because nothing can change it later.
- You need **snapshots or history**: undo stacks, versioned state, or
  time-travel debugging.
- You build lists by **prepending**, or consume them from the head. Both run
  in O(1).
- You like **functional-style pipelines**: `map`, `filter` and `reduce` that
  return new lists.
- You need a **cyclic** traversal (`CircularLinkedList`).

## When to choose something else

- **Frequent random access by index:** a plain PHP array gives O(1) index
  access.
- **Heavy appending at the end:** `ArrayStack` or `Queue` append in O(1).
- **Walking backward from a node:** `DoublyLinkedList` keeps a `previous`
  link on every node.
- **Fast membership checks:** `HashTable` or `Set`.
