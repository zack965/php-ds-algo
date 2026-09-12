# DoublyLinkedList: the same persistent pattern, plus `previous` pointers

`Zack\PhpDsAlgo\DataStructure\LinkedList\Doubly\DoublyLinkedList` is
`SingleLinkedList`'s sibling: same persistent (clone-then-splice) design,
same static-factory-only construction, same method surface (enforced by the
`IDoublyLinkedList` contract mirroring `ILinkedList`) — but every node also
carries a `previous` pointer that has to be kept consistent on both sides of
every splice.

If you haven't read [`01-single-linked-list.md`](01-single-linked-list.md)
yet, read that first — this article only calls out what's *different*.

## The node gets a second pointer

```php
class DoublyLinkedListNode
{
    public function __construct(
        private mixed $value,
        private ?DoublyLinkedListNode $next = null,
        private ?DoublyLinkedListNode $previous = null
    ) {}
    // getValue/getNext/setNext/getPrevious/setPrevious/__toString
}
```

`__toString()` prints `Node(value=..., previous=..., next=...)` — handy for
debugging a splice gone wrong, since you can see both neighbors at once.

## `cloneNodes()` now wires `previous` as it walks

```php
private function cloneNodes(): ?DoublyLinkedListNode
{
    if ($this->head === null) return null;
    $newHead = new DoublyLinkedListNode($this->head->getValue());
    $newCurrent = $newHead;
    $old = $this->head->getNext();
    while ($old !== null) {
        $node = new DoublyLinkedListNode($old->getValue());
        $newCurrent->setNext($node);
        $node->setPrevious($newCurrent);   // <-- the extra step vs SingleLinkedList
        $newCurrent = $node;
        $old = $old->getNext();
    }
    return $newHead;
}
```

Otherwise it's the identical single-pass forward walk.

## Splicing needs both neighbors updated — every time

This is the real difference from the singly-linked version. Anywhere
`SingleLinkedList` does one pointer reassignment, `DoublyLinkedList` needs
two (or four, when inserting in the middle). `insert()` is the clearest
example:

```php
public function insert(mixed $value, int $index): self
{
    // ... bounds check, prepend() fast path for index === 0 ...
    $newHead = $this->cloneNodes();
    $previousNode = $newHead;
    for ($i = 0; $i < $index - 1; $i++) {
        $previousNode = $previousNode->getNext();
    }
    $newNode = new DoublyLinkedListNode($value);

    $next = $previousNode->getNext();
    $newNode->setNext($next);
    $newNode->setPrevious($previousNode);
    $previousNode->setNext($newNode);
    if ($next !== null) {
        $next->setPrevious($newNode);   // fix the far side's back-pointer too
    }
    return new self($newHead, $this->length + 1);
}
```

Four pointer assignments instead of two, because both the node *before* and
the node *after* the insertion point need to agree on their new neighbor.
The same "both sides" bookkeeping shows up in `removeByValue()`/`removeAt()`
(the node being cut out has its neighbors re-linked to each other on both
`next` and `previous`) and in `prepend()`/`append()` (the new endpoint's
lone dangling pointer, plus the old endpoint's `previous`/`next` back to it).

## Where it still parallels `SingleLinkedList` exactly

- **`prepend()`** clones the *entire* chain (unlike `SingleLinkedList`,
  which shares the old suffix) — because the old head would need its
  `previous` pointer set to the new node, and mutating a node the old list
  object still references would break that list's own invariant. So
  `DoublyLinkedList::prepend()` is O(n), not O(1) like its singly-linked
  counterpart. This is the one real behavioral divergence between the two
  implementations, driven directly by needing back-pointers to stay correct.
- **`removeHead()`** likewise clones first (again because the new head's
  `previous` must become `null`, and that new head is a node from the old
  chain — mutating it in place would corrupt the original list), whereas
  `SingleLinkedList::removeHead()` skips the clone.
- **`reverse()`** walks with the same three-pointer pattern as the
  single-linked version, but also flips each node's `previous` to point at
  its (new) predecessor as it goes:

  ```php
  while ($current !== null) {
      $next = $current->getNext();
      $current->setNext($previous);
      if (!is_null($previous)) {
          $previous->setPrevious($current);
      }
      $previous = $current;
      $current = $next;
  }
  ```

  Note this method clones first (`$head = $this->cloneNodes();`) — unlike
  `SingleLinkedList::reverse()`, which reverses the live chain in place.
- `append`, `removeAt`, `removeTail`, `get`, `getTail`, `contains`,
  `indexOf`, `map`, `filter`, `reduce`, `toArray`, `toArrayValues`,
  `insertBeforeNode`, `insertAfterNode`, `clear`, `clearAndKeepHead` all
  mirror `SingleLinkedList` method-for-method (same static factories too:
  `of()`, `fromNodes()`/`ofObjects()`, `fromIterable()`, `empty()`) — the
  only change anywhere is the extra `previous` bookkeeping described above.
  One implementation detail worth knowing: `fromIterable()` on
  `DoublyLinkedList` indexes into `$values` with `$values[$i]` in a counted
  `for` loop (it needs random access to build `previous` links up front),
  so it expects an array-like iterable with sequential integer keys — a
  lazy `Generator` won't work here the way it does for
  `SingleLinkedList::fromIterable()`, which just `foreach`es once.

## Why keep two classes instead of one with an optional `previous`?

The codebase's own convention (per `CLAUDE.md`): each structure is a
self-contained pair of classes. A single class flag-toggling `previous`
support would make every method branch on "am I singly or doubly linked,"
which is exactly the kind of conditional complexity the persistent,
clone-then-splice pattern is trying to avoid. Duplicating the ~500 lines and
letting each stay a straight-line implementation of its own invariant is the
tradeoff this library makes consistently (same reasoning applies to
`ArrayStack` vs. `Queue` not sharing a base class either).

## Complexity summary

| Operation | Time | vs. SingleLinkedList |
|---|---|---|
| `prepend` | O(n) | O(1) in `SingleLinkedList` — back-pointer forces a full clone |
| `removeHead` | O(n) | O(1) in `SingleLinkedList` — same reason |
| `append`/`insert`/`removeAt`/`removeTail`/`removeByValue` | O(n) | same |
| `get`/`indexOf`/`contains` | O(n) | same — still no random access, no backward-walk shortcut used |
| `reverse` | O(n) | same complexity, but clones first instead of reversing in place |
| `map`/`filter`/`reduce` | O(n) | same |
