# HashTable & HashMap: separate-chaining hash structures, one a set, one a map

`Zack\PhpDsAlgo\DataStructure\HashTabe\HashTable` and `...\HashMap` are the
library's hash-based structures — both array-of-buckets, separate-chaining
designs sharing the same hashing approach, but with a different idea of what
gets hashed. `HashTable` is really a hash **set**: values act as their own
keys, so `insert('apple')` both stores `'apple'` and is what you look it up
by. `HashMap` is a proper key-value map: a key and a value are independent,
stored together in a small `HashMapNode` object, and only the *key* is ever
hashed. Both live in the same folder/namespace — note it's `HashTabe`, a
genuine typo (not one of the intentional `Algorythmes`-style misspellings
elsewhere in this library), but PSR-4 resolution depends on it matching
exactly. Both are **mutable** (`insert()`/`put()`/`delete()`/`clear()`
change the object in place) and both implement `IteratorAggregate` and
`Countable`, so `foreach`/`count()` work directly.

## Internal representation: an array of buckets

```php
// HashTable
private array $table;  // array<int, array<int, T>>

// HashMap
private array $table;  // array<int, list<HashMapNode<K, V>>>
```

Both constructors pre-size `$table` with `array_fill(0, $capacity, [])` —
`$capacity` empty buckets, ready to receive entries. `HashTable`'s buckets
hold raw values directly; `HashMap`'s hold `HashMapNode` objects pairing a
`readonly` key with a mutable value:

```php
class HashMapNode
{
    public function __construct(
        public readonly mixed $key,
        public mixed $value,
    ) {}

    public function getKey(): mixed { return $this->key; }
    public function getValue(): mixed { return $this->value; }
    public function setValue(mixed $value): void { $this->value = $value; }
}
```

`setValue()` is what lets `HashMap::put()`/`update()` replace a value
without touching the bucket structure at all — more on that below.

## Hashing: `serializeKey()` + `xxh3`, shared by both

Both classes reduce whatever they're hashing (a `HashTable` value, a
`HashMap` key) to a string, then hash that string:

```php
private function tableHash(mixed $value): int
{
    $hash = hash('xxh3', $this->serializeKey($value));
    $hashInteger = hexdec(substr($hash, 0, 8));
    return $hashInteger % $this->capacity;
}
```

`hash('xxh3', ...)` produces a 64-bit (16 hex-char) digest; only the first 8
hex chars (32 bits) are actually used as the bucket index — the rest of the
hash's entropy is discarded. That's a real trade-off (a fuller hash would
spread entries more evenly and cause fewer collisions) but not a
correctness issue: every lookup still resolves collisions by scanning the
bucket and comparing with `===`, so two values landing in the same bucket
just means one more comparison, not a wrong answer.

### What's hashable, and how `serializeKey()` decides

```php
private function serializeKey(mixed $value): string
{
    if (is_string($value)) {
        return $value;
    }
    if (is_resource($value)) {
        throw new InvalidArgumentException(/* ... */);
    }
    if (is_float($value) && $value === 0.0) {
        $value = 0.0; // canonicalize -0.0
    }
    try {
        return serialize($value);
    } catch (\Throwable $e) {
        throw new InvalidArgumentException(/* ... */, previous: $e);
    }
}
```

- **Strings** are used directly — no `serialize()` overhead, and it's the
  common case.
- **Other scalars, `null`, arrays, and objects** go through `serialize()`,
  which encodes type information into the string itself (`i:5;` vs `s:1:"5";`
  vs `b:1;` for `5`, `"5"`, and `true` respectively), so different types
  never accidentally collide *because* they'd otherwise stringify the same
  way.
- **Closures and resources are rejected** with `InvalidArgumentException` —
  neither has a stable value representation. `serialize()` throws its own
  `Exception` for a closure ("Serialization of 'Closure' is not allowed"),
  which `serializeKey()` catches and rewraps; resources are checked for
  explicitly beforehand, because `serialize()` does *not* throw for a
  resource — it silently serializes to the resource's numeric ID (`"i:0;"`
  style), which would be actively wrong to hash on: two unrelated resources
  can reuse the same ID once the first is closed.

Only `HashTable` values and `HashMap` *keys* go through `serializeKey()` — a
`HashMap` value is never hashed, so it has none of these restrictions:

```php
$map = new HashMap();
$map->put('handler', fn() => 'ok'); // fine — the closure is the value, not the key
$map->put(fn() => 'ok', 'value');   // throws InvalidArgumentException — closure as a key
```

### The `-0.0` trap this class had to account for

`-0.0 === 0.0` is `true` in PHP — the comparison both classes use for every
equality check. But `serialize(-0.0)` and `serialize(0.0)` produce different
strings (`"d:-0;"` vs `"d:0;"`), which would hash to different buckets
despite the values comparing equal — a direct violation of the one thing a
hash table has to guarantee (equal values land in the same bucket). This
was caught by testing, not by inspection: inserting `-0.0` and then calling
`hasValue(0.0)` came back `false` even though the values are `===`-equal.
The fix is the `is_float($value) && $value === 0.0` branch above —
canonicalize to positive zero before hashing, for both `HashTable` values
and `HashMap` keys.

## `HashTable`: insert, lookup, and the "value is its own key" model

```php
public function insert(mixed $value): void
{
    $this->addToBucket($value);
    if ($this->getLoadFactor() > 0.7) {
        $this->resize();
    }
}
```

`addToBucket()` appends to `$this->table[$this->tableHash($value)]` — that's
the entire insert. Duplicates are allowed and stored as separate entries
(this is a bag, not a set in the strict mathematical sense); `hasValue()`,
`delete()`, and `getValuePosition()` all follow the same two-step shape —
hash to a bucket, then linear-scan that bucket for a `===` match:

```php
public function hasValue(mixed $value): bool
{
    $index = $this->tableHash($value);
    foreach ($this->table[$index] as $item) {
        if ($item === $value) {
            return true;
        }
    }
    return false;
}
```

Because the equality check is always `===`, a hash *collision* (two
different values landing in the same bucket) is handled correctly by
scanning past the non-matching entries — separate chaining's whole point.
`update(mixed $oldValue, mixed $newValue)` is `delete($oldValue)` +
`addToBucket($newValue)` under the hood — since `$newValue` may hash to a
*different* bucket than `$oldValue`, this is a real move, not an in-place
edit.

## `HashMap`: keys are hashed, values are along for the ride

`HashMap`'s equivalent lookups hash the **key**, then compare
`$item->getKey() === $key`, never touching `getValue()` in the process:

```php
public function hasKey(mixed $key): bool
{
    $index = $this->tableHash($key);
    foreach ($this->table[$index] as $item) {
        if ($item->getKey() === $key) {
            return true;
        }
    }
    return false;
}
```

That asymmetry is why `hasValue()`/`getValuePosition()` on `HashMap` are
`O(n)` — a value was never hashed anywhere, so finding one means scanning
every bucket, not just one:

```php
public function hasValue(mixed $value): bool
{
    foreach ($this->table as $bucket) {
        foreach ($bucket as $node) {
            if ($node->getValue() === $value) {
                return true;
            }
        }
    }
    return false;
}
```

### `put()` upserts; `update()` requires the key to already exist

```php
public function put(mixed $key, mixed $value): void
{
    $position = $this->getKeyPosition($key);
    if ($position !== false) {
        $this->table[$position['bucketIndex']][$position['itemIndex']]->setValue($value);
        return;
    }
    $this->addToBucket(new HashMapNode($key, $value));
    if ($this->getLoadFactor() > 0.7) {
        $this->resize();
    }
}
```

If the key is already present, `put()` calls `setValue()` on the existing
`HashMapNode` in place — no new bucket entry, and critically, **no
load-factor check**, since the entry count didn't change. Only a genuine
new-key insert can push the load factor past `0.7` and trigger a `resize()`.
`update(mixed $key, mixed $value)` shares that same "replace in place" body
but returns `false` instead of inserting when the key is missing — the
"must already exist" counterpart to `put()`'s upsert. `get()` returns
`null` for a missing key (not an exception), mirroring `HashTable::getValue()`.

### A bug found and fixed here, worth knowing about

Both of the behaviors above are the *current, correct* behavior — but
`put()` originally **threw** `InvalidArgumentException` on a duplicate key
instead of replacing it, directly contradicting `IHashMap::put()`'s own
docblock ("If the key already exists, its associated value is replaced")
and just about every `put`/`set` convention in existence. `get()`
originally threw `OutOfBoundsException` for a missing key instead of
returning `null`, contradicting `IHashMap::get()`'s documented `@return
V|null`. Both were caught by writing tests directly against the interface's
stated contract rather than just the code's existing behavior — a reminder
that "the code does X" and "the code is *supposed* to do X" aren't the same
question. `tests/Unit/DataStructure/HashTabe/HashMapTest.php` has explicit
regression tests for both (`testPutReplacesValueForExistingKey`,
`testGetReturnsNullForMissingKey`), plus one for the `-0.0` fix above.

## `resize()`: shared growth strategy

```php
public function resize(): void
{
    $values = $this->getAllValues(); // or getAllEntries() for HashMap
    $this->capacity *= 2;
    $this->table = array_fill(0, $this->capacity, []);
    foreach ($values as $value) {
        $this->addToBucket($value);
    }
}
```

Identical shape in both classes: read every entry out, double the capacity,
rebuild an empty table at the new size, then re-insert everything. Bucket
assignment isn't stable across a resize — `% $this->capacity` changes for
most entries the moment `$this->capacity` does — so this is a genuine
re-bucketing, not just a bigger array. `insert()`/`put()` call this
automatically once the load factor exceeds `0.7`; it's also public, so
either structure can be grown pre-emptively before a known bulk insert.

## The one internal difference: how `delete()` leaves a bucket

`HashTable::delete()` uses `unset()`:

```php
unset($this->table[$index][$itemIndex]);
```

which removes the key but doesn't reindex the array — a bucket's keys can
have gaps after a deletion (e.g. `[0 => 'a', 2 => 'c']` if the middle entry
was removed). `HashMap::delete()` uses `array_splice()` instead:

```php
array_splice($this->table[$index], $itemIndex, 1);
```

which does reindex — a `HashMap` bucket's keys always stay a contiguous
0-based list. Neither behavior is a bug (both `foreach` correctly regardless
of gaps, and `addToBucket()`'s `[] =` append always picks the next key past
whatever's already there, gaps or not), but it means `getBucket()`'s return
type genuinely differs between the two: `array<int, T>` for `HashTable`,
`list<HashMapNode<K, V>>` for `HashMap`.

## `IteratorAggregate` / `Countable`: making `foreach`/`count()` work

```php
// HashTable
public function getIterator(): Traversable
{
    yield from $this->getAllValues();
}

// HashMap
public function getIterator(): Traversable
{
    foreach ($this->getAllEntries() as $entry) {
        yield $entry->getKey() => $entry->getValue();
    }
}
```

Both `count()` methods are one-line aliases for `getSize()`. The
interesting part is *where* `IteratorAggregate`/`Countable` are declared:
`IHashTable extends IteratorAggregate, Countable` and `IHashMap extends
IteratorAggregate, Countable` — at the **interface** level, not just
implemented on the concrete class. That matters because `Graph`/`IGraph`
got this wrong earlier in the library's history: `Graph` has a
`getIterator()` method, but `IGraph` doesn't extend `IteratorAggregate`, so
`foreach ($graph as ...)` silently iterates zero times through an
`IGraph`-typed reference (PHP falls back to iterating public properties —
`Graph` has none — instead of calling `getIterator()`). `HashTable`/`HashMap`
were deliberately built to avoid that trap, matching `IStack`'s (correct)
precedent instead.

`HashMap`'s `foreach` is also a small demonstration of a generator quirk
worth knowing generally: a `Generator`'s `yield $key => $value` is **not**
restricted to `int|string` keys the way a real PHP array's keys are —
`yield $arrayKey => $value` or `yield $someObject => $value` both work fine.
That's exactly what makes `foreach ($map as $key => $value)` a safe, correct
way to read a `HashMap` out even though `HashMap` allows array/object keys
that could never exist as real PHP array keys. It's also why `HashMap` has
no `toArray()`: a native `[key => value]` array literally cannot represent
a `HashMap` with non-`int|string` keys, so `getAllEntries()` /
`getAllKeys()` / `getAllValues()` / `foreach` are the only correct ways to
read one out in full.

## Complexity summary

| Operation | HashTable | HashMap | Notes |
|---|---|---|---|
| `insert` / `put` | O(1) amortized | O(1) amortized | O(n) on the rare resize |
| `hasValue` / `hasKey` | O(1) amortized | O(1) amortized | one bucket hash + scan |
| `hasValue` (on `HashMap`) | — | O(n) | values aren't hashed; scans every bucket |
| `get` / `getValue` | O(1) amortized | O(1) amortized | |
| `delete` | O(1) amortized | O(1) amortized | `HashMap` additionally reindexes the bucket via `array_splice()` |
| `update` | O(1) amortized | O(1) amortized | |
| `getValuePosition` (on `HashTable`) / `getKeyPosition` (on `HashMap`) | O(1) amortized | O(1) amortized | |
| `getValuePosition` (on `HashMap`) | — | O(n) | full scan, same reason as `hasValue` |
| `resize` | O(n) | O(n) | doubles capacity, re-buckets everything |
| `getAllValues` / `getAllKeys` / `getAllEntries` | O(n) | O(n) | |
| `getSize` / `count()` | O(capacity + size) | O(capacity + size) | walks every bucket and sums `count()`; not tracked incrementally |
| `isEmpty` | O(capacity + size) | O(capacity + size) | calls `getSize()` under the hood |
| `clear` / `reset` | O(capacity) | O(capacity) | reallocates the bucket array |

## Where this fits in the bigger picture

A hash table/map was, per this library's own `PathToOnePointO.md`, "the
single most conspicuous absence for a 'data structures' library" — every
other shipped structure up to this point was linear (lists, stack, queue)
plus one graph and one heap. `HashTable` and `HashMap` close that gap
together rather than separately, sharing one hashing implementation instead
of duplicating it: `HashTable` for the "do I have this value" / dedup-ish
use case, `HashMap` for genuine key-value lookups — including with keys
`array`/`int|string`-restricted native PHP arrays can't represent at all.
