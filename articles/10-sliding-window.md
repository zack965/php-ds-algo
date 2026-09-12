# SlidingWindow: fixed-size window traversal via callback

`Zack\PhpDsAlgo\Algorithmes\SlidingWindow::processFixedSizeSlidingWindow()`
is the simplest algorithm in the library, and the only one built around a
callback rather than returning a computed result directly:

```php
public static function processFixedSizeSlidingWindow(array $data, int $size, callable $callback)
{
    $count = count($data);
    if ($size <= 0 || $size > $count) {
        return;
    }
    for ($i = 0; $i <= $count - $size; $i++) {
        $window = array_slice($data, $i, $size);
        $callback($window, $i);
    }
}
```

For every valid starting index (`0` through `count($data) - $size`), it
slices out a `$size`-length window and hands it to `$callback($window,
$startIndex)`. The caller decides what to *do* with each window — sum it,
find its max, check a condition — rather than this method computing
anything itself. That makes it a general-purpose traversal primitive rather
than a single-purpose algorithm; e.g. "maximum sum subarray of size k" is
just:

```php
$best = PHP_INT_MIN;
SlidingWindow::processFixedSizeSlidingWindow($data, $k, function (array $window) use (&$best) {
    $best = max($best, array_sum($window));
});
```

## Why this isn't O(n) in the way "sliding window" usually implies

The classic sliding-window *technique* gets its efficiency from **not**
recomputing each window from scratch — you maintain a running sum/count and
just add the incoming element and subtract the outgoing one as the window
slides, turning an O(n·k) brute force into O(n). This implementation doesn't
do that: `array_slice($data, $i, $size)` reconstructs a whole new `$size`-
element array on every iteration, so the traversal itself is O(n·k), with
whatever the callback does on top. It's a straightforward, easy-to-read
window *enumerator* — useful for correctness and for callbacks that
genuinely need the whole window's contents (not just an aggregate) — but not
the optimized incremental technique the "sliding window" name usually
implies. If you need true O(n) behavior for an aggregate like sum/max,
you'd maintain the running value yourself around this call, or write a
dedicated incremental version.

## Guard behavior

`$size <= 0` or `$size > count($data)` returns immediately without invoking
the callback at all (no exception thrown) — silently a no-op rather than an
error, which is worth knowing if you're expecting a thrown
`InvalidArgumentException` the way the rest of this library tends to
respond to invalid input.

## Where this fits in the roadmap

Only the fixed-size variant exists today. The project's own backlog
(`PathToOnePointO.md`, `TODO.md`) lists dynamic/variable-size sliding window
and monotonic-deque min/max window as explicitly out-of-scope for the
current milestone — future additions, not gaps in what's already shipped.

## Complexity

O((n - k + 1) · k) for the traversal itself (dominated by the repeated
`array_slice` calls), plus whatever the callback does per window.
