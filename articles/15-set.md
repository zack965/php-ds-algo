# Set: an array-backed collection of unique values

`Zack\PhpDsAlgo\DataStructure\Set\Set` is the library's plain set — a
collection that only ever holds one of each value, plus the usual
set-algebra operations (`union`, `intersection`, `difference`,
`isSubsetOf`, `isSupersetOf`, `equals`). It implements `ISet`
(`Zack\PhpDsAlgo\Contracts\ISet`), extends `IteratorAggregate` and
`Countable` (same pattern `IHashTable`/`IHashMap` established — see
[`14-hashtable-hashmap.md`](14-hashtable-hashmap.md)), and is documented
generically (`@template T`) since PHP has no runtime generics: every
method is typed `mixed` in code, `T` only in PHPDoc. **Mutable**, like
`HashTable`/`HashMap`/the heaps — `add()`/`remove()`/`clear()` change the
set in place — but `union()`/`intersection()`/`difference()` are pure and
always return a **new** `Set`, never touching either operand.

## Internal representation: a de-duplicated list, no hashing

```php
private array $data = [];
```

That's the entire storage — a plain, 0-indexed PHP array. Unlike
`HashTable` (also conceptually a "set of unique values"), `Set` doesn't
hash anything: it doesn't bucket, it doesn't compute a bucket index, and
membership isn't O(1)-amortized. Every operation that needs to know "is
this value already here" — `add()`, `contains()`, `remove()`, and every
set-algebra method — does a **linear scan** over `$data` via
`GeneralArrayAlgorithms::contains()`, which compares with strict `===`:

```php
public static function contains(array $data, mixed $value): bool
{
    if ($value === null) {
        throw new InvalidArgumentException('Value cannot be null.');
    }

    foreach ($data as $entry) {
        if ($entry === $value) {
            return true;
        }
    }

    return false;
}
```

So `Set` trades `HashTable`'s O(1)-amortized membership check for
simplicity: no hashing scheme to design, no `serializeKey()`, no bucket
array, no resizing — just an array and a scan. That's a deliberate
trade-off, not an oversight; see [Complexity summary](#complexity-summary)
and [Set vs. HashTable](#set-vs-hashtable-when-to-reach-for-which) below
for when that trade-off matters.

Strict comparison also means `Set` treats `null` as an invalid element,
not as "no value" — `GeneralArrayAlgorithms::contains()` throws
`InvalidArgumentException` the moment `$value === null`, so `add(null)`,
`contains(null)`, and `remove(null)` all throw rather than silently
no-op.

## Construction: every value flows through `add()`

```php
public function __construct(array $data = [])
{
    foreach ($data as $value) {
        $this->add($value);
    }
}
```

There's no separate "de-duplicate the input array" step — the constructor
just calls `add()` for every element, and `add()`'s own membership check
does the de-duplication for free:

```php
public function add(mixed $value): bool
{
    if (GeneralArrayAlgorithms::contains($this->data, $value)) {
        return false;
    }
    $this->data[] = $value;
    return true;
}
```

```php
$set = new Set([1, 1, 2, 2, 2, 3]);
$set->getAll(); // [1, 2, 3] — duplicates collapsed, first-occurrence order kept
```

Because comparison is strict (`===`), values a looser comparison would
merge stay distinct:

```php
$set = new Set([1, '1', true]);
$set->count(); // 3 — int 1, string "1", and bool true are three different elements
```

This is the same "one code path, no special-casing" shape `HashMap`'s
`put()` uses for upserts — routing construction through the same method
that already enforces the invariant, instead of duplicating the check.

## Removal: why it has to reassign, not just call

```php
public function remove(mixed $value): bool
{
    if (!GeneralArrayAlgorithms::contains($this->data, $value)) {
        return false;
    }
    $this->data = GeneralArrayAlgorithms::remove($this->data, $value);
    return true;
}
```

`GeneralArrayAlgorithms::remove()` is a **pure** function — it takes an
array by value (no `&`), builds a filtered copy, and returns it:

```php
public static function remove(array $data, mixed $value): array
{
    foreach ($data as $key => $entry) {
        if ($entry === $value) {
            unset($data[$key]);
        }
    }

    return array_values($data);
}
```

PHP arrays are value types, so the `$data` inside `remove()` is a copy the
moment it's passed in — mutating it (via `unset()`) never touches the
caller's array. `Set::remove()` has to capture and reassign the return
value (`$this->data = GeneralArrayAlgorithms::remove(...)`) for the
removal to actually stick; calling `GeneralArrayAlgorithms::remove()` and
discarding its result — an easy mistake, since the method *name* reads
like it mutates — would leave `$this->data` completely unchanged while
still reporting `true`. Worth internalizing before writing similar
array-manipulating helpers: in this codebase, "operates on an `array`
parameter" defaults to pure/return-a-new-array (`GeneralArrayAlgorithms`,
`ArraySortAlgorythmes`, `ArraySearchAlogorthme` are all this shape), so any
caller that wants the mutation has to explicitly reassign.

`array_values()` at the end also means `$data`'s keys are always
re-indexed to a contiguous `0..n-1` list after a removal — unlike
`HashTable::getBucket()`, which can develop key gaps after a `delete()`
since it removes with a bare `unset()` (see
[`14-hashtable-hashmap.md`](14-hashtable-hashmap.md)).

## Set algebra: pure methods, new instances

`union()`, `intersection()`, and `difference()` never mutate `$this` or
`$other` — each builds a fresh values list and wraps it in `new self(...)`:

```php
public function union(ISet $other): ISet
{
    $result = new self($this->getAll());

    foreach ($other->getAll() as $value) {
        $result->add($value);
    }

    return $result;
}

public function intersection(ISet $other): ISet
{
    $values = [];
    $otherValues = $other->getAll();

    foreach ($this->getAll() as $value) {
        if (GeneralArrayAlgorithms::contains($otherValues, $value)) {
            $values[] = $value;
        }
    }

    return new self($values);
}
```

`difference()` is `intersection()`'s mirror image — same shape, with the
condition inverted (`!GeneralArrayAlgorithms::contains(...)`) — which is
also why it's directional: `$a->difference($b)` (everything in `$a` not in
`$b`) and `$b->difference($a)` (everything in `$b` not in `$a`) are
generally two different sets, not a symmetric pair.

```php
$a = new Set([1, 2, 3]);
$b = new Set([2, 3, 4]);

$a->union($b)->getAll();        // [1, 2, 3, 4]
$a->intersection($b)->getAll(); // [2, 3]
$a->difference($b)->getAll();   // [1]        — in $a, not in $b
$b->difference($a)->getAll();   // [4]        — in $b, not in $a
```

`isSubsetOf()`/`isSupersetOf()` are the boolean-returning cousins of the
same scan — no new `Set` is built, they just short-circuit `false` on the
first value that breaks the relationship:

```php
public function isSubsetOf(ISet $other): bool
{
    $thisValues = $this->getAll();
    $otherValues = $other->getAll();

    foreach ($thisValues as $value) {
        if (!GeneralArrayAlgorithms::contains($otherValues, $value)) {
            return false;
        }
    }

    return true;
}
```

An empty set trivially satisfies this — the `foreach` never runs, so it
falls straight through to `return true`, matching the mathematical fact
that ∅ is a subset of every set.

`equals()` checks size first (O(1) via `count()`, cheaper than a scan) and
only falls through to the element-by-element check if the sizes already
match — since every element is already known-unique on both sides, "same
size + every element of `$this` found in `$other`" is sufficient for set
equality without needing to check the reverse direction too:

```php
public function equals(ISet $other): bool
{
    if ($this->count() !== $other->count()) {
        return false;
    }

    foreach ($this->data as $value) {
        if (!GeneralArrayAlgorithms::contains($other->getAll(), $value)) {
            return false;
        }
    }

    return true;
}
```

## Iteration: `IteratorAggregate` over a plain `ArrayIterator`

```php
public function getIterator(): Traversable
{
    return new ArrayIterator($this->data);
}
```

`ISet extends IteratorAggregate` at the **interface** level (not just
implemented on the class) — the same deliberate choice `IHashTable`/
`IHashMap` made, and the one `IGraph` notoriously didn't (see
[`14-hashtable-hashmap.md`](14-hashtable-hashmap.md) and the `Graph`
gotcha in the README): declaring `getIterator()` without the interface
extending `IteratorAggregate` means `foreach` on an interface-typed
reference silently iterates zero times instead of calling the method.
`foreach ($set as $value)` yields raw values in insertion order — not
nodes, not key-value pairs.

## Complexity summary

| Operation | Cost | Notes |
|---|---|---|
| `add` | O(n) | scans `$data` for a duplicate before appending |
| `contains` | O(n) | linear scan, strict `===` |
| `remove` | O(n) | one scan to check membership, one to filter |
| `clear` | O(1) | reassigns `$data = []` |
| `isEmpty` / `count` | O(1) | `count($this->data)` |
| `getAll` | O(n) | `array_values()` copy — a snapshot, safe to mutate |
| `union` | O(n·m) | n = `$this`'s size, m = `$other`'s size (each `add()` scans) |
| `intersection` / `difference` | O(n·m) | one scan of `$other` per element of `$this` |
| `isSubsetOf` / `isSupersetOf` | O(n·m) | short-circuits on first mismatch |
| `equals` | O(1) best case, O(n·m) worst | size check first; full scan only if sizes match |

## Set vs. HashTable: when to reach for which

Both `Set` and `HashTable` are, structurally, "a collection that dedupes
values" — so it's worth being explicit about why this library has both.
`HashTable` hashes each value into a bucket (`xxh3`, truncated to 32 bits,
modulo capacity) and auto-resizes past a 0.7 load factor, giving
O(1)-amortized `insert()`/`hasValue()`/`delete()` at the cost of the
hashing machinery described in
[`14-hashtable-hashmap.md`](14-hashtable-hashmap.md) — including its
`serializeKey()` rules (closures/resources rejected, `-0.0` canonicalized
to `0.0`). `Set` skips all of that: no hashing, no buckets, no resize
logic, just a scanned array — O(n) per operation, but simpler to reason
about and with fewer restrictions on what a value can be (no
closure/resource carve-out, since nothing needs to be reduced to a hashable
string; `null` is the only value `Set` rejects, and only because
`GeneralArrayAlgorithms::contains()` uses it as its own "no value found"
sentinel internally). For small sets, or where the value type doesn't fit
`HashTable`'s hashability rules, `Set`'s simplicity wins; for anything
large or lookup-heavy, `HashTable` is the better fit.

## A sharp edge worth knowing: `remove()`'s discard-the-return-value trap

`Set::remove()` briefly shipped with exactly the bug described above under
[Removal](#removal-why-it-has-to-reassign-not-just-call) — calling
`GeneralArrayAlgorithms::remove($this->data, $value)` without assigning
the result back to `$this->data`. Because `GeneralArrayAlgorithms::remove()`
is pure, that version of `remove()` always returned `true` for a value
that *was* present, while silently leaving it in the set — `contains()`
and `count()` would disagree with what `remove()` had just claimed to do.
The fix is the one-line reassignment shown above; it's called out
explicitly here because the same shape of bug (discarding a pure helper's
return value) is easy to reintroduce in any future method that calls
`GeneralArrayAlgorithms::remove()` or a similarly pure array helper.

## Where this fits in the bigger picture

`Set` isn't one of the categories `PathToOnePointO.md`'s M2 milestone
calls out by name (that list is `HashTable`/`HashMap`, done; Binary Search
Tree, Deque, heap sort, topological sort, one DP algorithm, one string
algorithm — still open) — it's an extra, smaller structure that rounds out
the "collections" corner of the library alongside `HashTable`. It's also
a good illustration of this codebase's general split between mutable
containers (`Set` itself: `add`/`remove`/`clear` change it in place) and
pure helpers (`GeneralArrayAlgorithms`: every method returns a new value,
never mutates its arguments) — the same split documented for
`ArraySortAlgorythmes`/`ArraySearchAlogorthme` in
[`00-overview.md`](00-overview.md), just showing up one layer down inside
a data structure's own implementation this time.
