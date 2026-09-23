# HashTable & HashMap

**Namespace:** `Zack\PhpDsAlgo\DataStructure\HashTabe`
**Classes:** `HashTable`, `HashMap`, `HashMapNode`
**Contracts:** `IHashTable`, `IHashMap`

## What it is

A **hash table** runs each item through a *hash function* to pick a
**bucket**, so it can find, add or remove the item in roughly constant time,
however large the collection grows.

The library provides two hash structures that share the same hashing engine:

- **`HashTable`** stores **values**. The value is its own key. Use it to
  answer "have I seen this?"
- **`HashMap`** stores **key → value pairs**. Only the key is hashed, and the
  value can be anything.

Both are **mutable**, grow automatically, and implement `IteratorAggregate`
and `Countable`, so `foreach` and `count()` work on them directly.

## How it works

- **Buckets:** an array of `capacity` buckets. The default capacity is 10.
- **Hashing:** strings are hashed directly. Other scalars, arrays and
  objects are serialized first, so values of different types stay distinct:
  `5`, `"5"` and `true` land in different places. The key is hashed with the
  fast `xxh3` algorithm and mapped to a bucket.
- **Collisions:** handled by **separate chaining**. Each bucket holds a list,
  and lookups compare items with strict equality (`===`) inside that bucket.
- **Growth:** when the load factor (items ÷ buckets) goes above **0.7**, the
  capacity doubles and every entry is placed into the larger table. Lookups
  stay fast as the collection grows.
- **Normalization:** `-0.0` and `0.0` hash to the same bucket, matching PHP's
  `===`.
- **Supported keys:** strings, numbers, booleans, `null`, arrays and objects.
  Closures and resources cannot be hashed, so they are rejected with
  `InvalidArgumentException`. In a `HashMap` they are welcome as **values**.

## HashTable

```php
use Zack\PhpDsAlgo\DataStructure\HashTabe\HashTable;

$seen = new HashTable();          // optional capacity: new HashTable(64)
$seen->insert('apple');
$seen->insert('banana');

$seen->hasValue('apple');  // true
$seen->delete('apple');    // true
$seen->hasValue('apple');  // false
$seen->count();            // 1
```

### API

```php
$table->insert($value);
$table->hasValue($value);
$table->getValue($value);            // the stored value, or null
$table->delete($value);              // bool
$table->update($oldValue, $newValue); // bool
$table->getValuePosition($value);    // ['bucketIndex' => …, 'itemIndex' => …] or false
$table->getAllValues();
$table->getSize();                   // also count($table)
$table->getCapacity();
$table->getLoadFactor();
$table->isEmpty();
$table->resize();                    // grow ahead of a large batch
$table->clear();
$table->reset();
foreach ($table as $value) { … }
```

## HashMap

```php
use Zack\PhpDsAlgo\DataStructure\HashTabe\HashMap;

$ages = new HashMap();
$ages->put('alice', 30);
$ages->put('bob', 25);
$ages->put('alice', 31);   // replaces the existing value

$ages->get('alice');       // 31
$ages->get('carol');       // null
$ages->hasKey('bob');      // true

foreach ($ages as $name => $age) {
    echo "$name: $age\n";
}
```

### Rich keys

Keys can be **arrays or objects**, which native PHP arrays cannot use as
keys:

```php
$distances = new HashMap();
$distances->put([0, 0], 'origin');
$distances->put([3, 4], 5.0);

$distances->get([3, 4]); // 5.0
```

`foreach` yields these keys unchanged. Read a whole map with `foreach`,
`getAllEntries()`, `getAllKeys()` or `getAllValues()`.

### API

```php
$map->put($key, $value);       // insert, or replace if the key exists
$map->update($key, $value);    // replace only; returns false if the key is missing
$map->get($key);               // value, or null
$map->hasKey($key);
$map->hasValue($value);
$map->delete($key);            // bool
$map->getKeyPosition($key);
$map->getValuePosition($value);
$map->getAllEntries();         // list of HashMapNode (getKey(), getValue())
$map->getAllKeys();
$map->getAllValues();
$map->getSize();               // also count($map)
$map->getCapacity();
$map->getLoadFactor();
$map->isEmpty();
$map->resize();
$map->clear();
$map->reset();
```

## Complexity

| Operation | HashTable | HashMap |
|---|---|---|
| `insert` / `put` | O(1) amortized | O(1) amortized |
| `hasValue` / `hasKey` | O(1) average | O(1) average |
| `get` / `getValue` | O(1) average | O(1) average |
| `delete` / `update` | O(1) average | O(1) average |
| `hasValue` / `getValuePosition` on a map (search by value) | — | O(n) |
| `resize` | O(n) | O(n) |
| `getAllValues` / `getAllKeys` / `getAllEntries` | O(n) | O(n) |
| `getSize` / `isEmpty` | O(capacity + n) | O(capacity + n) |
| `clear` / `reset` | O(capacity) | O(capacity) |

Space: O(capacity + n).

## When to use HashTable

- **Deduplication:** skip items you have already processed.
- **Visited tracking** in crawlers, graph searches and recursive walks.
- **Fast membership checks** against large allow lists or block lists.
- **Counting distinct items** as they stream in.

## When to use HashMap

- **Caches and memoization:** map inputs to computed results. Array keys
  work well for multi-argument function calls.
- **Indexes:** look up records by ID, email or any other field.
- **Counting and grouping:** word frequencies, grouping rows by category.
- **Composite keys:** coordinates, tuples or value objects as keys.
- **Configuration and registries,** including callables stored as values.

## When to choose something else

- **Values kept in sorted order, or range queries** ("all keys between 10
  and 20"): `BinarySearchTree`.
- **Union, intersection and difference between collections:** `Set`.
- **Small collections where insertion order matters most:** `Set` or a plain
  array.
- **Always taking the smallest or highest-priority item:** `MinHeap`,
  `MaxHeap` or `PriorityQueue`.
