# Sliding Window

**Namespace:** `Zack\PhpDsAlgo\Algorithmes`
**Class:** `SlidingWindow`

## What it is

The **sliding window** technique looks at a fixed-size run of consecutive
elements, a *window*, then moves that window one position at a time across
the data. Many "subarray of size k" problems reduce to examining every such
window.

`SlidingWindow::processFixedSizeSlidingWindow()` walks every window of a
given size and passes each one to a callback. **You decide** what to compute
per window: a sum, a maximum, an average, or a pattern check.

```php
use Zack\PhpDsAlgo\Algorithmes\SlidingWindow;

SlidingWindow::processFixedSizeSlidingWindow(
    [1, 3, 2, 6, 4],
    3,
    function (array $window, int $start) {
        echo $start . ': ' . implode(', ', $window) . PHP_EOL;
    }
);
// 0: 1, 3, 2
// 1: 3, 2, 6
// 2: 2, 6, 4
```

## Signature

```php
SlidingWindow::processFixedSizeSlidingWindow(
    array $data,
    int $size,
    callable $callback // function (array $window, int $startIndex)
);
```

- For an array of `n` elements, the callback runs **n − size + 1** times.
- Each call receives the window's **full contents** and its **start index**.
- If `$size` is `0`, negative, or larger than the array, the method returns
  without calling the callback. Your code does not need a special case for
  those inputs.

## Examples

**Maximum sum of k consecutive elements**
```php
$best = PHP_INT_MIN;
SlidingWindow::processFixedSizeSlidingWindow($data, $k, function (array $window) use (&$best) {
    $best = max($best, array_sum($window));
});
```

**Moving average (for example a 7-day average)**
```php
$averages = [];
SlidingWindow::processFixedSizeSlidingWindow($dailyValues, 7, function (array $window) use (&$averages) {
    $averages[] = array_sum($window) / count($window);
});
```

**Find where a condition first holds**
```php
$found = null;
SlidingWindow::processFixedSizeSlidingWindow($readings, 5, function (array $window, int $i) use (&$found) {
    if ($found === null && min($window) > 100) {
        $found = $i; // first 5-reading stretch entirely above 100
    }
});
```

## Complexity

- **Time:** O((n − k + 1) · k) to build the windows, plus the callback's own
  work.
- **Space:** O(k) for the current window.

## When to use it

- **Moving averages and rolling statistics** over time series: stock prices,
  metrics, sensor data.
- **Fixed-length pattern checks:** "are there 3 failed logins in any 3
  consecutive events?"
- **Max or min sum of a subarray of size k.**
- **Signal smoothing and simple feature extraction.**
- Any calculation that needs the **whole window's contents**, such as
  medians, distinct counts or custom rules, rather than a single running
  number.

## When to choose something else

- **Only a running total over a very large series:** keep a running sum
  yourself. Add the element that enters the window and subtract the one that
  leaves.
- **Windows whose size changes with the data** ("smallest subarray with sum ≥
  S"): use a two-pointer loop that grows and shrinks the window as it goes.
- **Searching for a text pattern in a string:** use [KMP](17-kmp.md).
