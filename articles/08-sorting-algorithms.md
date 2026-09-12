# ArraySortAlgorythmes: seven classic sorts on plain PHP arrays

`Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes` (note the intentional
misspelling — see `CLAUDE.md`) is a static-method utility class implementing
bubble, selection, insertion, merge, quick, heap, and bucket sort. All
operate on plain `array`s and return a new sorted array. All rely on
`AlgorythmesGlobalHelpers::swapValuesOfArray()` for in-place element swaps
except merge sort (doesn't need swapping) and bucket sort (delegates to
insertion sort per bucket instead of swapping directly).

## The three O(n²) sorts

All three take a snapshot copy of the array (PHP arrays are value types
passed by value into these methods, so mutating the local `$nums`/`$data`
parameter never touches the caller's array) and sort it in place before
returning it.

**Bubble sort** — repeated adjacent-pair comparison passes:
```php
for ($i = 1; $i < $length_nums; $i++) {
    for ($j = 0; $j < $length_nums - 1; $j++) {
        if ($nums[$j] > $nums[$j + 1]) {
            AlgorythmesGlobalHelpers::swapValuesOfArray($nums, $j, $j + 1);
        }
    }
}
```
No early-exit optimization (no "no swaps this pass, stop early" flag) — it
always runs the full `n-1` outer passes regardless of whether the array
became sorted earlier. O(n²) time in every case, including an
already-sorted input.

**Selection sort** — for each position, find the true minimum of the
remaining unsorted suffix and swap it into place:
```php
for ($i = 0; $i <= $length_nums - 1; $i++) {
    $minimumIndex = $i;
    for ($j = $i + 1; $j <= $length_nums - 1; $j++) {
        if ($nums[$j] < $nums[$minimumIndex]) {
            $minimumIndex = $j;
        }
    }
    if ($minimumIndex !== $i) {
        AlgorythmesGlobalHelpers::swapValuesOfArray($nums, $i, $minimumIndex);
    }
}
```
Always O(n²) comparisons regardless of input order (it has to scan the
whole remaining suffix every time to find the minimum), but at most `n`
swaps — the guard `if ($minimumIndex !== $i)` skips the no-op swap when the
current position is already the minimum.

**Insertion sort** — grows a sorted prefix one element at a time, sliding
each new element left past anything bigger than it:
```php
for ($i = 1; $i <= $length_nums - 1; $i++) {
    $j = $i;
    while ($j > 0 && $nums[$j - 1] > $nums[$j]) {
        AlgorythmesGlobalHelpers::swapValuesOfArray($nums, $j, $j - 1);
        $j = $j - 1;
    }
}
```
This implementation does the "slide left" via repeated adjacent swaps
rather than the more classic "shift right, then single insert" — functionally
equivalent but does more array writes per shift. Best case is O(n) (already
sorted — the `while` never enters), worst case O(n²) (reverse sorted).

(A top-level `Zack\PhpDsAlgo\SortingAlgorithms` used to duplicate this
class's selection sort under a different namespace; it's been deleted —
`ArraySortAlgorythmes::selectionSort()` is simply the only copy now.)

## Merge sort — top-down recursive, O(n log n) guaranteed

```php
public static function MergeSort(array $data): array
{
    if (empty($data) || count($data) == 1) return $data;
    self::processMergeSort($data, 0, count($data) - 1);
    return $data;
}
private static function processMergeSort(array &$data, int $low, int $height)
{
    if ($low >= $height) return;
    $mid = (int) floor(($low + $height) / 2);
    self::processMergeSort($data, $low, $mid);
    self::processMergeSort($data, $mid + 1, $height);
    self::Merge($data, $low, $mid, $height);
}
```

Standard divide-and-conquer: split at the midpoint, recursively sort each
half, then merge the two sorted halves back together in linear time. The
whole `$data` array is passed **by reference** (`array &$data`) through the
recursive helpers, so the split/merge happens against one shared array
rather than allocating new sub-arrays at every level — this is what keeps
the auxiliary space closer to the merge step's own `$temp` buffer rather
than the classic O(n log n) space you'd get from array-slicing at each
recursive call. `Merge()` itself is the textbook two-pointer merge: walk
`$left`/`$right` pointers across the two sorted subranges, always taking
the smaller front element into `$temp`, then copy over whatever's left of
either side, then write `$temp` back into `$data[$low..$height]`.

Guaranteed O(n log n) time regardless of input order, O(n) auxiliary space
for the `$temp` buffer (rebuilt per merge call, not shared across calls),
and it's **stable** (the `<=` comparison in `Merge()` — `if ($data[$left]
<= $data[$right])` — takes from the left side on ties, preserving original
relative order of equal elements).

## Quick sort — Hoare-style partitioning around a fixed pivot

```php
public static function QuickSOrt(array $data): array
{
    if (empty($data) || count($data) == 1) return $data;
    self::prociessQuickSort($data, 0, count($data) - 1);
    return $data;
}
```

`partition()` always picks `$data[$left]` (the first element of the current
subrange) as the pivot — not a random or median-of-three pivot — which
means an already-sorted or reverse-sorted input triggers quicksort's O(n²)
worst case (every partition splits off just one element). The partition
scheme itself is a Hoare-style two-pointer sweep from both ends toward the
middle, swapping out-of-place pairs, finishing by swapping the pivot into
its final resting position `$rt`:

```php
private static function partition(array &$data, int $left, int $right): int
{
    $pivot = $data[$left];
    $lt = $left + 1;
    $rt = $right;
    while ($lt <= $rt) {
        while ($lt <= $rt && $data[$lt] <= $pivot) $lt++;
        while ($rt >= $left && $data[$rt] > $pivot) $rt--;
        if ($lt < $rt) AlgorythmesGlobalHelpers::swapValuesOfArray($data, $lt, $rt);
    }
    AlgorythmesGlobalHelpers::swapValuesOfArray($data, $left, $rt);
    return $rt;
}
```

Recursion is on `[$left, $pivot-1]` and `[$pivot+1, $right]`, same
by-reference `$data` array as merge sort. Average case O(n log n), worst
case O(n²) on already-sorted/reverse-sorted/all-equal inputs (a known,
inherent property of first-element-pivot quicksort, not a bug) — pick merge
sort instead if input order can't be assumed random.

## Heap sort — extract-min repeatedly, backed by `MinHeap`

```php
public static function heapSort(array $data): array
{
    $sorted = [];
    $minHeap = new MinHeap($data);
    $size = $minHeap->size();
    for ($i = 0; $i < $size; $i++) {
        $sorted[] = $minHeap->extract();
    }
    return $sorted;
}
```

The simplest implementation in this class, because it doesn't reimplement
the heap logic at all — it builds a `MinHeap` (see
[`13-heap.md`](13-heap.md)) directly from `$data`, then calls `extract()`
exactly `size()` times. Every `extract()` pops the current minimum and
re-heapifies, so the sequence of extracted values comes out ascending "for
free." O(n log n) time in every case (`n` extractions, each O(log n)); O(n)
space for the heap's own backing array, separate from `$data`, which is
never mutated. Bubble/selection/insertion sort all avoid allocating a
second O(n) structure — heap sort trades that extra space for a worst-case
guarantee those three don't have.

## Bucket sort — partition into equal-width ranges, sort each independently

```php
public static function bucketSort(array $data): array
{
    foreach ($data as $value) {
        if (!is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException(/* ... */);
        }
    }

    $n = count($data);
    if ($n < 2) return $data;

    $minMaxData = AlgorythmesGlobalHelpers::getMinAndMax($data);
    $min = $minMaxData["min"];
    $max = $minMaxData["max"];
    $range = $max - $min;
    $bucketCount = max(1, (int) floor(sqrt($n)));
    $bucketWidth = ($range == 0) ? 0 : $range / $bucketCount;

    $buckets = array_fill(0, $bucketCount, []);
    foreach ($data as $value) {
        if ($range == 0) {
            $index = 0;
        } else {
            $index = (int) floor(($value - $min) / $bucketWidth);
            if ($index >= $bucketCount) {
                $index = $bucketCount - 1;
            }
        }
        $buckets[$index][] = $value;
    }

    $sorted = [];
    foreach ($buckets as $bucket) {
        if (empty($bucket)) continue;
        $sorted[] = count($bucket) === 1 ? [$bucket[0]] : static::insertionSort($bucket);
    }

    return array_merge(...$sorted);
}
```

Every element is validated up front — anything that isn't an `int` or
`float` (strings, including numeric ones, bools, `null`, arrays, objects)
throws `InvalidArgumentException` before any bucketing work runs. `NAN`
and `INF`/`-INF` pass that check (they're still floats) but aren't
meaningfully sortable in practice — `NAN`'s comparisons are always `false`.

The strategy: find the array's min/max (`AlgorythmesGlobalHelpers::getMinAndMax()`),
pick `bucketCount = max(1, floor(sqrt(n)))` — the standard choice for
uniformly distributed input, since it makes both the bucket count and each
bucket's expected population O(sqrt(n)) — then slice the value range into
that many equal-width buckets. Every value's bucket index is
`floor((value - min) / bucketWidth)`; the value equal to `max` would
compute one index past the last bucket, so it's clamped back into it. When
every value is identical (`range == 0`), everything routes to bucket 0
directly instead of dividing by a zero-width bucket — note the `==`
(loose) comparison here specifically, not `===`: `$range` can come out as
either an `int` or a `float` `0` depending on whether `$min`/`$max` are
ints or floats, and only the loose comparison catches both.

Each non-empty bucket is sorted independently with `insertionSort()` (a
single-element bucket skips the call, trivially already sorted), then all
buckets are concatenated in ascending index order via `array_merge()` —
correct because every value in bucket *i* is `<=` every value in bucket
*i+1* by construction, regardless of how unevenly the input clusters across
buckets. Average case O(n) for uniformly distributed input (each bucket's
`insertionSort()` runs on a small, roughly constant-size slice); worst case
O(n²) if every value lands in one bucket (e.g. many duplicates, or a
tightly clustered distribution) — bucket sort's whole advantage depends on
the input actually spreading across buckets.

## Complexity summary

| Algorithm | Best | Average | Worst | Space | Stable? |
|---|---|---|---|---|---|
| Bubble sort | O(n²)* | O(n²) | O(n²) | O(1) | yes |
| Selection sort | O(n²) | O(n²) | O(n²) | O(1) | no (swap-based) |
| Insertion sort | O(n) | O(n²) | O(n²) | O(1) | yes |
| Merge sort | O(n log n) | O(n log n) | O(n log n) | O(n) | yes |
| Quick sort | O(n log n) | O(n log n) | O(n²) | O(log n) call stack | no |
| Heap sort | O(n log n) | O(n log n) | O(n log n) | O(n) heap | no |
| Bucket sort | O(n) | O(n) | O(n²)** | O(n + bucketCount) | yes (insertion-sort-per-bucket) |

\* This implementation's bubble sort has no early-exit flag, so it's O(n²)
even on already-sorted input — the one place this codebase's implementation
is strictly worse than the textbook optimal version of the same algorithm.

\*\* Bucket sort's worst case is entirely distribution-dependent, not
input-order-dependent like the others in this table — a heavily clustered
or heavily duplicated input degrades toward one bucket doing all the work,
regardless of whether that input happens to already be sorted.
