# ArraySearchAlogorthme: seven search strategies over sorted (mostly) arrays

`Zack\PhpDsAlgo\Algorithmes\ArraySearchAlogorthme` (misspelling intentional,
matches the folder/namespace convention) implements binary, exponential,
interpolation, jump, linear, ternary, and Fibonacci search. All but linear
search require the input to already be sorted ascending; all return the
found index, or `-1` if the target isn't present.

## `binarySearch` — recursive, classic

```php
public static function binarySearch(array $nums, int $target, int $start, int $end = 0): int
{
    if ($start > $end) return -1;
    $middle = $start + intdiv($end - $start, 2);
    if ($nums[$middle] == $target) return $middle;
    elseif ($nums[$middle] > $target) return self::binarySearch($nums, $target, $start, $middle - 1);
    else return self::binarySearch($nums, $target, $middle + 1, $end);
}
```

Recursive rather than iterative; `$middle` is computed as `$start +
intdiv($end - $start, 2)` (not the more overflow-prone `($start + $end) /
2`) — a deliberate, correct choice, since it avoids intermediate sums that
could exceed integer range on very large indices. Note the parameter order:
`$start` has **no default** but `$end` defaults to `0` — so a top-level call
must always pass `$start` explicitly (typically `0`) and usually `$end`
explicitly too (typically `count($nums) - 1`); relying on the `$end = 0`
default only makes sense for a single-element range check, not as a
"search the whole array" shortcut.

## `linearSearch` — the only one that doesn't require sorted input

```php
public static function linearSearch(array $data, int|string $target): int|string
{
    if (empty($data)) return -1;
    foreach ($data as $index => $value) {
        if ($value === $target) return $index;
    }
    return -1;
}
```

Works on associative arrays too — returns whatever key matched (`int|string`
return type), not necessarily a numeric position. Strict (`===`) comparison,
unlike most of the other search methods here which use loose `==`. O(1)
best case, O(n) average/worst.

## `exponentialSearchImplementation` — find a range, then binary search it

```php
public static function exponentialSearchImplementation(array $nums, int $target): int
{
    if ($nums == null || empty($nums)) return -1;
    if ($nums[0] == $target) return 0;
    $i = 1;
    $numsLength = count($nums);
    while ($i < $numsLength && $nums[$i] <= $target) {
        $i = $i * 2;
    }
    return self::binarySearch($nums, $target, $i / 2, min($i, $numsLength - 1));
}
```

Doubles `$i` (1, 2, 4, 8, ...) until it either runs off the array or
overshoots the target, giving an O(log(position of target)) range
`[i/2, min(i, n-1)]` to binary-search — better than plain binary search when
the target is near the front of a very large array. One quirk worth
knowing: `$i / 2` is PHP's `/` operator, which returns a `float`
(`int / int` isn't guaranteed to divide evenly — e.g. if the `while` loop
body never runs, `$i` stays `1` and `$i / 2` is `0.5`), and that float gets
passed into `binarySearch(int $start, ...)`. This file has no `declare(strict_types=1)`,
so PHP coerces it to an `int` automatically (truncating `0.5` to `0`), but on
PHP 8.1+ that non-integral coercion raises a deprecation notice. Functionally
harmless here since `binarySearch`'s own `$start > $end` guard still resolves
correctly, but it's a rough edge — `intdiv($i, 2)` would be the cleaner
choice.

## `interpolationSearchRecursive` — estimate, don't just bisect

```php
$pos = $low + (($target - $data[$low]) * ($high - $low)) / ($data[$high] - $data[$low]);
```

Instead of always checking the midpoint, interpolation search estimates
*where the target should be* assuming roughly uniform value distribution
between `$data[$low]` and `$data[$high]` — a straight-line interpolation
formula. When the data really is uniformly distributed, this gives O(log
log n) average performance, beating binary search's O(log n); on
non-uniform data it degrades toward O(n). Recurses into `[pos+1, high]` or
`[low, pos-1]` depending on which side the estimate landed short/long of the
target. Division by `$data[$high] - $data[$low]` means this breaks (division
by zero) if the two boundary values are equal — callers need distinct
boundary values, which is guaranteed as long as the array isn't full of
duplicates spanning the whole search range.

## `jumpSearch` — block-skip then linear-scan the block

```php
public static function jumpSearch(array $data, int $target, int $jumpSize, int $start, int $end, int $jumpIndex)
```

Unlike the other methods, `jumpSearch` takes all of its traversal state
(`$jumpSize`, `$start`, `$end`, `$jumpIndex`) as required parameters — there's
no simpler "just pass the array and target" entry point; callers must
compute the initial block bounds themselves (conventionally `$jumpSize =
(int) sqrt(count($data))`, `$start = 0`, `$end = $jumpSize - 1`,
`$jumpIndex = 0`) before the first call, since block search is optimal at
block size √n. It uses `AlgorythmesGlobalHelpers::isBetween($target,
$data[$start], $data[$end])` to test whether the target could be in the
current block, linear-scans that block if so, or recurses forward to the
next block (`$newStart = $end + 1`, `$newEnd = $newStart + $jumpSize - 1`)
if not. O(√n) time — better than linear, worse than binary, but each
"step" is cheaper than a full comparison-heavy binary-search step, which is
why it's useful when comparisons are expensive (e.g. seeking on disk/tape,
the classic motivating use case).

## `TernarySearchAlgorythme` — split into thirds instead of halves

```php
$mid1 = $low + intdiv($high - $low, 3);
$mid2 = $high - intdiv($high - $low, 3);
```

Checks both `$mid1` and `$mid2` per call and recurses into whichever third
the target must fall in (`< mid1`, `> mid2`, or the middle third between
them). Despite doing 3-way division, ternary search does **not** beat
binary search in practice — it makes ~2 comparisons per level against
binary search's 1, over `log₃n` levels instead of `log₂n`, which works out
to *more* total comparisons asymptotically (`2 log₃ n > log₂ n`). It's
included here as a well-known algorithm-family entry, not because it's the
faster choice.

## `FibonacciSearchALgorythme` — binary search using Fibonacci-sized steps

Finds the smallest Fibonacci number ≥ `count($data)` via
`getClosestFibonacci()`, then probes at Fibonacci-offset positions,
shrinking the search window by the next-smaller Fibonacci number each step
instead of always halving — same O(log n) asymptotic complexity as binary
search, but historically preferred on hardware where addition/subtraction
are cheaper than division (mid-point computation needs no `/2`). This is the
most involved method in the file; if you're extending it, `getClosestFibonacci()`
is the piece worth understanding first — it returns `['f1' => Fm, 'f2' =>
Fm-1, 'f3' => Fm-2]` for the smallest Fibonacci triple where `Fm >= n`.

## Complexity summary

| Algorithm | Requires sorted? | Time | Notes |
|---|---|---|---|
| `linearSearch` | no | O(n) | only one usable on unsorted/associative data |
| `binarySearch` | yes | O(log n) | recursive |
| `exponentialSearchImplementation` | yes | O(log i) where i = target's position | good when target is near the front |
| `interpolationSearchRecursive` | yes, ~uniform | O(log log n) avg, O(n) worst | breaks on equal boundary values |
| `jumpSearch` | yes | O(√n) | caller must precompute block bounds |
| `TernarySearchAlgorythme` | yes | O(log₃ n) calls, more total comparisons than binary | included for completeness, not speed |
| `FibonacciSearchALgorythme` | yes | O(log n) | addition/subtraction only, no division |

See [`08-sorting-algorithms.md`](08-sorting-algorithms.md) for the sorts
these searches assume as a precondition, and
[`../src/Helpers/Algorythmes/AlgorythmesGlobalHelpers.php`](../src/Helpers/Algorythmes/AlgorythmesGlobalHelpers.php)
for the shared `isBetween()`/`swapValuesOfArray()` primitives both files
lean on.
