# Set

**Namespace:** `Zack\PhpDsAlgo\DataStructure\Set`
**Class:** `Set`
**Contract:** `Zack\PhpDsAlgo\Contracts\ISet`

## What it is

A **set** is a collection of **unique** values. Adding a value that is
already present has no effect. Sets also support the classic **set algebra**
operations: union, intersection, difference, subset and superset checks, and
equality.

`Set` stores its values in insertion order and compares them with strict
equality (`===`). It is **mutable** for `add`, `remove`, `update` and
`clear`. The algebra methods (`union`, `intersection`, `difference`) are
**pure**: they return a **new** `Set` and leave both operands unchanged.

```php
use Zack\PhpDsAlgo\DataStructure\Set\Set;

$set = new Set([1, 1, 2, 2, 2, 3]);
$set->getAll(); // [1, 2, 3]

$set->add(4);    // true
$set->add(4);    // false, already present
$set->contains(2); // true
$set->remove(2);   // true
```

## How it works

- Values live in a plain, zero-indexed array.
- Every value you add goes through `add()`, which checks for an existing copy
  first. The constructor uses `add()` too, so duplicates in the input
  collapse automatically.
- Comparison is **strict**, so `1`, `'1'` and `true` are three different
  elements:

```php
(new Set([1, '1', true]))->count(); // 3
```

- `null` is not a valid element. `add(null)`, `contains(null)` and
  `remove(null)` throw `InvalidArgumentException`.

## API

### Membership & mutation
```php
$set->add($value);                 // bool: true if added
$set->remove($value);              // bool: true if removed
$set->update($oldValue, $newValue); // bool: replace in place, keeping uniqueness
$set->contains($value);
$set->clear();
```

### Access
```php
$set->getAll();        // list of values, in insertion order
$set->get($index);     // value at a position (OutOfBoundsException if missing)
$set->indexOf($value); // position, or false
$set->count();         // also count($set)
$set->isEmpty();
foreach ($set as $value) { … }
```

### Set algebra
```php
$a = new Set([1, 2, 3]);
$b = new Set([2, 3, 4]);

$a->union($b)->getAll();        // [1, 2, 3, 4]
$a->intersection($b)->getAll(); // [2, 3]
$a->difference($b)->getAll();   // [1]  (in $a, not in $b)
$b->difference($a)->getAll();   // [4]  (in $b, not in $a)

(new Set([2, 3]))->isSubsetOf($a); // true
$a->isSupersetOf(new Set([1]));    // true
$a->equals(new Set([3, 2, 1]));    // true (order does not matter)
```

The empty set is a subset of every set, as in mathematics.

## Complexity

n = size of this set, m = size of the other set.

| Operation | Time |
|---|---|
| `add` / `contains` / `remove` / `update` | O(n) |
| `indexOf` | O(n) |
| `get` | O(1) |
| `isEmpty` / `count` / `clear` | O(1) |
| `getAll` | O(n) |
| `union` | O(n · m) |
| `intersection` / `difference` | O(n · m) |
| `isSubsetOf` / `isSupersetOf` | O(n · m), stops at the first mismatch |
| `equals` | O(1) when sizes differ, otherwise O(n · m) |

Space: O(n).

## When to use it

- **Comparing collections:** shared tags between two articles, permissions a
  user has versus the ones a role requires, features added or removed
  between two versions.
- **Unique lists that keep insertion order:** selected filters, recently used
  items, chosen options.
- **Permission and access checks** using `isSubsetOf` and `isSupersetOf`.
- **Small and medium collections** where readable set-algebra code matters
  most.
- **Values of any type,** including closures, compared by identity.
- **Position-aware unique lists:** `get($index)` and `indexOf()` give you
  positional access as well.

## When to choose something else

- **Very large collections with frequent membership checks:** `HashTable`
  offers O(1) average lookups.
- **Pairing each item with extra data:** `HashMap`.
- **Duplicates allowed:** a plain array, `Queue` or a linked list.
- **Values kept in sorted order:** `BinarySearchTree` stores unique values
  and reads them back sorted.
