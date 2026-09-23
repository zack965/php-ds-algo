# Searching Algorithms

**Namespace:** `Zack\PhpDsAlgo\Algorithmes`
**Class:** `ArraySearchAlogorthme`

## What it is

`ArraySearchAlogorthme` is a static utility class with seven search
strategies for PHP arrays. Each returns the **index** of the target, or
`-1` when the target is not present.

Linear search works on any array. The other six take advantage of **sorted,
ascending** input to find the target in far fewer steps. To prepare data,
see [Sorting Algorithms](08-sorting-algorithms.md).

```php
use Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme;

$data = [2, 5, 8, 12, 16, 23, 38, 56, 72, 91];
$n = count($data);

ArraySearchAlogorthme::linearSearch($data, 23);                         // 5
ArraySearchAlogorthme::binarySearch($data, 23, 0, $n - 1);              // 5
ArraySearchAlogorthme::exponentialSearchImplementation($data, 23);      // 5
ArraySearchAlogorthme::interpolationSearchRecursive($data, 23, 0, $n - 1); // 5
ArraySearchAlogorthme::TernarySearchAlgorythme($data, 23);              // 5
ArraySearchAlogorthme::FibonacciSearchALgorythme($data, 23);            // 5

$block = (int) sqrt($n);
ArraySearchAlogorthme::jumpSearch($data, 23, $block, 0, $block - 1, 0); // 5
```

## Linear search

`linearSearch(array $data, int|string $target)` checks each element in turn
and compares with strict equality (`===`). It works on **unsorted** and
**associative** arrays, and returns the matching key, which can be an `int`
or a `string`.

- **Use it for:** unsorted data, small arrays, associative arrays, and
  one-off lookups where sorting first would cost more than the search.
- **Choose something else for:** repeated lookups on large data. Sort once
  and use binary search, or store the data in a `HashMap` or `HashTable`.

## Binary search

`binarySearch(array $nums, int $target, int $start, int $end)` compares the
target with the **middle** element and discards the half that cannot
contain it. It repeats until it finds the target or the range is empty.
Pass the full range as `0` and `count($nums) - 1`.

- **Use it for:** the standard, reliable choice for sorted arrays of any
  size.
- **Choose something else when:** the data is unsorted. Use linear search.

## Exponential search

`exponentialSearchImplementation(array $nums, int $target)` checks positions
1, 2, 4, 8, … until it passes the target, then runs binary search inside
that final range. Its cost depends on the target's **position** rather than
on the array size.

- **Use it for:** very large sorted arrays where targets tend to be **near
  the beginning**, and unbounded or streaming sorted sequences.
- **Choose something else when:** targets are evenly spread through the
  array. Plain binary search is equally effective there.

## Interpolation search

`interpolationSearchRecursive(array $data, int $target, int $low, int $high)`
**estimates** where the target should be from its value, the way you open a
phone book near "S" when looking up "Smith". On evenly distributed data it
averages **O(log log n)**.

- **Use it for:** large sorted arrays of **uniformly distributed** numbers,
  such as sequential IDs, timestamps at regular intervals, or evenly spaced
  measurements.
- **Choose something else for:** skewed or clustered data, or ranges with
  long runs of repeated values. Binary search suits those better.

## Jump search

`jumpSearch(array $data, int $target, int $jumpSize, int $start, int $end, int $jumpIndex)`
jumps ahead in fixed-size **blocks** until it finds the block that could hold
the target, then scans that block element by element. The best block size
is √n:

```php
$block = (int) sqrt(count($data));
ArraySearchAlogorthme::jumpSearch($data, $target, $block, 0, $block - 1, 0);
```

- **Use it for:** sorted data where **moving backward is expensive** or
  where sequential reads are cheaper than random access, such as data read
  from disk, tape or a forward-only stream.
- **Choose something else for:** in-memory arrays with cheap random access.
  Binary search takes fewer steps.

## Ternary search

`TernarySearchAlgorythme(array $data, int|string $target)` splits the range
into **three parts** with two midpoints and recurses into the part that must
contain the target.

- **Use it for:** learning divide-and-conquer, and as the basis for
  *ternary search on functions*, which finds the maximum or minimum of a
  unimodal function.
- **Choose something else for:** plain lookups in sorted arrays. Binary
  search makes fewer comparisons in total.

## Fibonacci search

`FibonacciSearchALgorythme(array $data, int $target)` narrows the search
range using **Fibonacci numbers** instead of halves. It needs only addition
and subtraction to compute positions. `getClosestFibonacci($n)` returns the
Fibonacci triple it starts from.

- **Use it for:** environments where **division is costly**, and data where
  accessing nearby elements is cheaper than accessing distant ones.
- **Choose something else for:** everyday PHP code. Binary search is the
  simplest O(log n) option.

## Complexity summary

| Algorithm | Needs sorted input | Time | Space |
|---|---|---|---|
| Linear | no | O(n) | O(1) |
| Binary | yes | O(log n) | O(log n) recursion |
| Exponential | yes | O(log i), where i = target position | O(log i) |
| Interpolation | yes, uniform values | O(log log n) average, O(n) worst | O(log n) recursion |
| Jump | yes | O(√n) | O(√n) recursion |
| Ternary | yes | O(log₃ n) steps | O(log n) recursion |
| Fibonacci | yes | O(log n) | O(1) |

## Picking a search

| Situation | Recommended |
|---|---|
| Unsorted or associative data | Linear |
| Sorted data, general case | Binary |
| Huge sorted data, target likely near the start | Exponential |
| Sorted, evenly distributed numbers | Interpolation |
| Sequential or forward-only access | Jump |
| Division is expensive | Fibonacci |
| Many repeated lookups by key | `HashMap` / `HashTable` instead of searching |
