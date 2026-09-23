# Sorting Algorithms

**Namespace:** `Zack\PhpDsAlgo\Algorithmes`
**Class:** `ArraySortAlgorythmes`

## What it is

`ArraySortAlgorythmes` is a static utility class with seven classic sorting
algorithms. Each one takes a plain PHP array and **returns a new array sorted
in ascending order**. The array you pass in is left unchanged.

```php
use Zack\PhpDsAlgo\Algorithmes\ArraySortAlgorythmes;

$data = [5, 2, 9, 1, 7];

ArraySortAlgorythmes::bubbleSort($data);    // [1, 2, 5, 7, 9]
ArraySortAlgorythmes::selectionSort($data);
ArraySortAlgorythmes::insertionSort($data);
ArraySortAlgorythmes::MergeSort($data);
ArraySortAlgorythmes::QuickSOrt($data);
ArraySortAlgorythmes::heapSort($data);
ArraySortAlgorythmes::bucketSort($data);
```

## Bubble sort

Bubble sort repeatedly steps through the array and swaps each pair of
neighbors that are out of order. After each pass, the largest remaining
value has "bubbled" to its final position at the end.

- **Use it for:** teaching, very small arrays, and visualizing how sorting
  works.
- **Choose something else for:** anything beyond a few dozen elements.
  Insertion sort and merge sort do less work.

## Selection sort

For each position, selection sort finds the smallest value in the unsorted
remainder and swaps it into place. It makes **at most n swaps**.

- **Use it for:** small arrays, and situations where **writes are expensive**
  (for example flash memory), since the swap count is minimal.
- **Choose something else when:** the input is already partly sorted.
  Insertion sort takes advantage of that, while selection sort always does
  the same number of comparisons.

## Insertion sort

Insertion sort grows a sorted prefix one element at a time. It slides each
new element left until it sits in the right place. On data that is already
sorted it runs in **linear time**.

- **Use it for:** small arrays, **nearly sorted** data, and data that arrives
  one item at a time. It is also used inside other sorts: `bucketSort` uses it
  to sort each bucket.
- **Choose something else for:** large arrays in random order. Use merge
  sort, quick sort or heap sort.

## Merge sort

Merge sort is a divide-and-conquer algorithm. It splits the array in half,
sorts each half recursively, then **merges** the two sorted halves in linear
time. The split and merge steps work on a single shared array.

It is **stable**: equal elements keep their original relative order.

- **Use it for:** large arrays that need **guaranteed O(n log n)**
  performance whatever the input order, and any case where **stability**
  matters, such as sorting records that were already ordered by another
  field.
- **Choose something else when:** memory is very tight. Merge sort uses O(n)
  extra space for its merge buffer.

## Quick sort

Quick sort picks a **pivot** (the first element of each range), then
partitions the range so smaller values end up on the left and larger values
on the right, and recurses into each side. The partition scans from both
ends toward the middle.

- **Use it for:** general-purpose sorting of **randomly ordered** data. It
  has excellent average performance and low memory overhead.
- **Choose something else when:** the input is already sorted or reverse
  sorted. Because the pivot is the first element, those inputs are best
  handled by merge sort or heap sort.

## Heap sort

Heap sort builds a `MinHeap` from the data in O(n), then **extracts the
minimum** repeatedly. The values come out in ascending order. See
[Heap](13-heap.md).

- **Use it for:** **guaranteed O(n log n)** time on any input distribution
  or order. It is also a clear illustration of how a heap produces sorted
  output.
- **Choose something else when:** you need stability. Merge sort is stable
  and has the same O(n log n) guarantee.

## Bucket sort

Bucket sort splits the value range into **√n equal-width buckets**, puts
each value into its bucket, sorts each bucket with insertion sort, then
joins the buckets in order. Every value in bucket *i* is ≤ every value in
bucket *i + 1*, so the joined result is fully sorted.

It accepts **integers and floats**. Any other type raises
`InvalidArgumentException` before sorting starts. Arrays where every value
is the same are handled directly.

```php
ArraySortAlgorythmes::bucketSort([0.42, 0.32, 0.23, 0.52, 0.25]);
// [0.23, 0.25, 0.32, 0.42, 0.52]
```

- **Use it for:** numeric data **spread evenly across a known range**, such as
  percentages, normalized scores, sensor readings or prices within a band.
  On such data it runs in near-linear time.
- **Choose something else when:** values are clustered tightly together or
  have many duplicates. Merge sort or heap sort keep O(n log n) on those
  distributions.

## Complexity summary

| Algorithm | Best | Average | Worst | Extra space | Stable |
|---|---|---|---|---|---|
| Bubble sort | O(n²) | O(n²) | O(n²) | O(1) | yes |
| Selection sort | O(n²) | O(n²) | O(n²) | O(1) | no |
| Insertion sort | O(n) | O(n²) | O(n²) | O(1) | yes |
| Merge sort | O(n log n) | O(n log n) | O(n log n) | O(n) | yes |
| Quick sort | O(n log n) | O(n log n) | O(n²) | O(log n) | no |
| Heap sort | O(n log n) | O(n log n) | O(n log n) | O(n) | no |
| Bucket sort | O(n) | O(n) | O(n²) | O(n) | yes |

## Picking a sort

| Situation | Recommended |
|---|---|
| Small array (< ~20 items) | Insertion sort |
| Nearly sorted data | Insertion sort |
| Large array, stability needed | Merge sort |
| Large array, random order | Quick sort |
| Guaranteed O(n log n) on any input | Merge sort or heap sort |
| Uniformly distributed numbers | Bucket sort |
| Minimizing writes | Selection sort |
| Learning or teaching sorting | Bubble sort, then the rest |

Sorted arrays are the starting point for the fast
[searching algorithms](09-searching-algorithms.md).
