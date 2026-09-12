# SingleLinkedList & CircularLinkedList: immutable singly-linked lists

`Zack\PhpDsAlgo\DataStructure\LinkedList\Single\SingleLinkedList` is a
persistent (copy-on-write-by-hand) singly-linked list. "Persistent" here
means every method that looks like it mutates the list — `append`,
`prepend`, `insert`, `removeAt`, `reverse`, `map`, `filter` — actually
returns a **brand new** `SingleLinkedList`, leaving the receiver's chain of
`SingleLinkedListNode`s exactly as it was.

## Shape

Two classes:

- `SingleLinkedListNode` — a plain box: `value` (mixed) + `next`
  (`?SingleLinkedListNode`), with getters/setters and a `__toString()` for
  debugging (`Node(value=1, next=2)`).
- `SingleLinkedList` — holds a private `?head` and `int $length`. The
  constructor is `private`; the only ways to build one are the static
  factories:
  - `SingleLinkedList::empty()` — empty list.
  - `SingleLinkedList::of([1, 2, 3])` — builds fresh nodes from raw values.
  - `SingleLinkedList::fromNodes([$n1, $n2])` / `ofObjects()` — takes
    pre-built `SingleLinkedListNode` objects and links them together
    (mutates the *nodes passed in* to set their `next` pointers — the nodes
    you hand it become the list's actual chain).
  - `SingleLinkedList::fromIterable($generator)` — builds from any
    `iterable`, one node per yielded value, in a single pass.

`SingleLinkedList` implements PHP's `IteratorAggregate`, so `foreach ($list
as $node)` works directly and yields **nodes** (not raw values) — use
`toArrayValues()` if you just want the values.

## The core trick: `cloneNodes()`

Because operations must not mutate the receiver, every method that changes
the chain's shape starts by deep-copying it:

```php
private function cloneNodes(): ?SingleLinkedListNode
{
    if ($this->head === null) {
        return null;
    }
    $newHead = new SingleLinkedListNode($this->head->getValue());
    $newCurrent = $newHead;
    $old = $this->head->getNext();
    while ($old !== null) {
        $node = new SingleLinkedListNode($old->getValue());
        $newCurrent->setNext($node);
        $newCurrent = $node;
        $old = $old->getNext();
    }
    return $newHead;
}
```

This walks the original chain once, allocating a fresh `SingleLinkedListNode`
per value, and returns the head of an entirely independent chain holding the
same values. Every mutating method then works exclusively on that clone (or,
where possible, avoids cloning altogether — see below) and wraps the result
in `new self($newHead, $newLength)`.

Two operations skip the clone as a fast path:

- **`prepend($value)`** doesn't need to touch existing nodes at all — it
  builds one new node whose `next` points at `$this->head` (the *old* chain,
  shared structurally with the new list) and returns `new self($newHead,
  $length + 1)`. Because nothing downstream of the old head is ever mutated
  by any other method (all of them clone first), sharing that suffix is
  safe.
- **`append($value)` on an empty list** just returns `new self($newNode, 1)`
  — nothing to clone.

Every other insert/remove path clones first, then splices:

```php
public function insert(mixed $value, int $index): self
{
    if ($index < 0 || $index > $this->length) {
        throw new InvalidArgumentException(ErrorMessages::INDEX_OUT_OF_BOUND);
    }
    if ($index === 0) {
        return $this->prepend($value);
    }
    $newHead = $this->cloneNodes();
    $previousNode = $newHead;
    for ($i = 0; $i < $index - 1; $i++) {
        $previousNode = $previousNode->getNext();
    }
    $newNode = new SingleLinkedListNode($value);
    $newNode->setNext($previousNode->getNext());
    $previousNode->setNext($newNode);
    return new self($newHead, $this->length + 1);
}
```

`insertBeforeNode($target, $value)` / `insertAfterNode($target, $value)` are
thin wrappers: they call `indexOf($target)` (throws if not found) and
delegate to `insert()` at that index (or `index + 1` for "after").

## Removal

`removeByValue`, `removeAt`, `removeHead`, `removeTail` all follow the same
shape: clone, walk to the node *before* the target, re-point its `next` to
skip the target, return a new list with `length - 1`. `removeHead()` is the
cheap exception — no clone needed, it just returns `new self($this->head
->getNext(), $length - 1)`, again relying on the fact that nothing mutates a
shared suffix. `clear()` resets to `empty()`; `clearAndKeepHead()` returns a
new one-node list holding just the current head's value.

All removal/access operations throw `InvalidArgumentException` (using the
centralized `ErrorMessages` constants — `LINKEDLIST_IS_EMPTY`,
`INDEX_OUT_OF_BOUND`, `NO_NODE_WITH_THIS_VALUE`) rather than returning null
or a sentinel, so callers don't need to `null`-check.

## Access & search

`get(int $index)`, `getTail()`, `contains($value)`, `indexOf($value)` are all
straightforward O(n) chain walks from `head`, each throwing the appropriate
`ErrorMessages` constant when the list is empty or the target isn't found.
Note `contains()`/`indexOf()` use loose `==` comparison, not `===`.

## Transformations

- **`reverse()`** is the classic three-pointer iterative reversal
  (`previous`/`current`/`next`), but notice it does **not** call
  `cloneNodes()` first — it walks and re-links `$this->head`'s actual nodes
  in place, then wraps the new tail-turned-head in `new self(...)`. This is
  the one method where the "receiver is untouched" guarantee is achieved by
  a different means worth knowing: since every node's `next` pointer gets
  reassigned during the walk, the *old* `SingleLinkedList` object still
  holds a reference to `$this->head`, but that node's `next` now points
  backward through the reversed chain — so treating the *old* `$a` as still
  representing `[1,2,3]` after calling `$a->reverse()` is not safe if you
  also hold a raw node reference from before the call. In practice this is
  invisible because nothing else retains raw nodes across a `reverse()`
  call, but it's the one place the implementation trades strict persistence
  for a simpler in-place two-pointer algorithm.
- **`toArray()`** / **`toArrayValues()`** — O(n) walk collecting nodes or
  values respectively.
- **`map(callable $fn)`** / **`filter(callable $fn)`** — walk the chain,
  collect into a plain PHP array, then rebuild via `self::of($values)`
  (i.e., always produce a fresh chain rather than mutating).
- **`reduce(callable $fn, $initial = null)`** — standard left fold,
  `$carry = $fn($carry, $value)` per node.

## Complexity summary

| Operation | Time | Notes |
|---|---|---|
| `prepend` | O(1) | shares the old chain's suffix |
| `append` | O(n) | clone + walk to tail |
| `insert($v, $i)` | O(n) | clone + walk to index |
| `get`/`indexOf`/`contains` | O(n) | linear walk, no random access |
| `removeHead` | O(1) | no clone needed |
| `removeAt`/`removeTail`/`removeByValue` | O(n) | clone + walk |
| `reverse` | O(n) | in-place three-pointer, no clone |
| `map`/`filter`/`reduce` | O(n) | single walk |

Every mutating op that clones pays O(n) purely for the copy, on top of
whatever walk the operation itself needs — this is the standard cost of
persistence over a linked structure. `DoublyLinkedList` (see the next
article) follows the identical pattern with the added bookkeeping of
`previous` pointers.

## Circular variant: `CircularLinkedList`

`Zack\PhpDsAlgo\DataStructure\LinkedList\Single\CircularLinkedList` reuses
`SingleLinkedListNode` and follows the exact same persistent pattern above
— private constructor, static factories, clone-then-splice on every
mutating method — with one structural difference: the tail's `next` points
back to the head instead of to `null`. For a non-empty list, `getTail()->
getNext() === getHead()` always holds; there's no natural end to stop at.

That one difference ripples into a few mechanical changes:

- **`cloneNodes()` loops exactly `$this->length` times** (`for ($i = 1; $i
  < $this->length; $i++)`) instead of walking until `getNext()` returns
  `null` — there's no `null` to walk into — and finishes by linking the new
  tail's `next` back to the new head, so the clone stays circular too.
- **`getIterator()` is bounded by `$length`**, not a `while ($current !==
  null)` loop: `for ($i = 0; $i < $this->length; $i++) { yield $current;
  $current = $current->getNext(); }`. Every other traversal in the class
  (`toArray()`, `toArrayValues()`, `map()`, `filter()`, `reduce()`,
  `contains()`, `indexOf()`) follows the same length-bounded shape for the
  same reason — a naive null-check loop here would simply never terminate.
- **Every mutating method has to re-link the tail back to the head** after
  splicing, on top of whatever `SingleLinkedList`'s equivalent method
  already does. `append()`, for instance, clones, walks to the (old) tail,
  attaches the new node, and then still has to point the *new* node's
  `next` back at the (cloned) head — one extra step `SingleLinkedList::append()`
  never needs.

That last point is exactly where a real bug lived until it was found by
writing tests for this class: `insert($value, $index)` computes the new
list's tail by walking forward from the new head — but for a genuine
*middle* insert (`0 < $index < length`, i.e. neither the `prepend()`
fast path nor the append-equivalent `$index === $length` case), the walk
used the **old** length as its step count instead of the new (one-longer)
length. That undercounted by exactly one step every time, so the
`CircularLinkedList` returned from a middle `insert()` had the *correct*
chain (walking forward from the head, by hand, still visited every value
in the right order and wrapped back to the head correctly) but a **wrong**
`$tail` reference — `getTail()` would return the second-to-last node, and
that node's `next` pointed at the real last node instead of back at the
head, silently breaking the "tail wraps to head" invariant for exactly the
node the list itself claimed was the tail. Fixed by walking the new length
(`$this->length`, i.e. `$i < $this->length + 1`) instead of the old one.
`insertBeforeNode()`/`insertAfterNode()` (which both delegate to `insert()`)
inherited the same bug for any target that wasn't the head or the tail.

Otherwise, the method surface and every complexity figure in the table
above carry over unchanged — same factories, same
insertion/removal/access/transformation/functional methods, same
`ErrorMessages`-backed exceptions, same O(n) clone-then-splice cost per
mutating call.
