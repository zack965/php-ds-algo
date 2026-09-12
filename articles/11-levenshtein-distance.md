# LevenshteinDistance: full DP table + reconstructed edit path

`Zack\PhpDsAlgo\Algorithmes\LevenshteinDistance::calculate($word1, $word2)`
is the library's one dynamic-programming algorithm. Unlike a typical
Levenshtein implementation that just returns an integer, this one returns
the **entire annotated DP matrix** plus a **step-by-step reconstructed edit
path** (the actual sequence of match/substitute/insert/delete operations
that achieves the minimum distance):

```php
return [
    'WordsData' => $wordsData,
    'matrix' => $matrix,
    'path' => $path,
    'minimumEditDistance' => (int)$matrix[$wordsData['Xrows']][$wordsData['YColumns']],
];
```

## The DP table's unusual encoding: letters live *inside* the matrix

Most textbook implementations keep the words in separate variables and only
put numbers in the DP table. This implementation instead builds a matrix
where **row 0 and column 0 hold the actual characters** of the two words,
and the real distance values start one row/column further in. `getProcessedWordData()`
prepends a leading space to each word first (`' ' . $word1`), which is what
creates a clean placeholder for the "empty prefix" case:

```php
$matrix[0][0] = '-';
// row 0, columns 1..YColumns: '-', then ' ', then each character of word2
for ($col = 1; $col <= $wordsData['YColumns']; $col++) {
    $matrix[0][$col] = $wordsData['Word2Spaced'][$col - 1];
}
// column 0, rows 1..Xrows: '-', then ' ', then each character of word1
for ($row = 1; $row <= $wordsData['Xrows']; $row++) {
    $matrix[$row][0] = $wordsData['Word1Spaced'][$row - 1];
}
```

`drawBorders()` then fills in the classic DP base case — "distance from the
empty string" — but shifted into **column 1** and **row 1** rather than
column/row 0, because column/row 0 are already occupied by the letters:

```php
for ($i = 1; $i <= $wordsData['Xrows']; $i++) { $matrix[$i][1] = (string)($i - 1); }
for ($i = 1; $i <= $wordsData['YColumns']; $i++) { $matrix[1][$i] = (string)($i - 1); }
```

So the *real* Levenshtein numbers occupy `matrix[j][i]` for `j` in
`[1..Xrows]`, `i` in `[1..YColumns]` — visually a standard DP grid, just
offset by one extra layer that carries the source characters for
convenience. This lets `processCell()` look up "which two characters am I
comparing" and "what are my three DP neighbors" from the *same* matrix
without needing separate lookups into `$word1`/`$word2`.

## Filling the table: the standard recurrence

```php
private static function processCell(array &$matrix, int $i, int $j): void
{
    $topRef = $matrix[0][$i];      // character from word2
    $leftRef = $matrix[$j][0];     // character from word1
    $topValue = (int)$matrix[$j - 1][$i];
    $leftValue = (int)$matrix[$j][$i - 1];
    $diagonalValue = (int)$matrix[$j - 1][$i - 1];
    $cost = ($topRef === $leftRef) ? 0 : 1;
    $matrix[$j][$i] = (string)min(
        $diagonalValue + $cost,  // match/substitute
        $topValue + 1,           // delete
        $leftValue + 1           // insert
    );
}
```

This is the textbook Levenshtein recurrence — `min(diagonal + (0 if same
char else 1), up + 1, left + 1)` — just reading its three neighbors and two
compared characters straight out of the same annotated matrix instead of
from separate structures. `buildTable()` drives this cell-by-cell across
`$j` (word1 positions) × `$i` (word2 positions), so the whole fill is
O(len(word1) × len(word2)).

## Reconstructing the edit path by walking backward

Once the table is filled, `buildingOptimalPathOfChanges()` walks **backward**
from the bottom-right corner (`$i = YColumns, $j = Xrows`) toward the
top-left, at each cell deciding which of the three recurrence terms actually
produced the stored value — that tells you which single-character edit
happened at this position:

```php
if ($isMatch && $current === $diagonal)        step = Match,      move diagonally
elseif (!$isMatch && $current === $diagonal+1) step = Substitute, move diagonally
elseif ($current === $left + 1)                step = Insert,     move left
elseif ($current === $top + 1)                 step = Delete,     move up
else throw new RuntimeException('No valid predecessor found.');
```

Each iteration appends a `['step' => ..., 'from' => ..., 'to' => ...,
'direction' => ...]` entry to `$steps` and moves the traceback pointer
accordingly, continuing until both `$i` and `$j` have walked back to `1`
(the row/column-1 base-case border). The `RuntimeException` guard is purely
defensive — it should be unreachable given a correctly filled table, since
every cell's value is, by construction, one of those three candidate
expressions.

The returned `path` is therefore an ordered list of operations, e.g.
turning `"cat"` into `"cut"` would produce a single `Substitute` step
(`c` stays, `a`→`u`, `t` stays), while `"cat"` → `"cats"` produces a single
`Insert`. This makes the return value useful for more than just the number —
you get a full diff-style explanation of *how* to get from one string to
the other.

## Complexity

- **Time:** O(m·n) to fill the table (`m = len(word1)+1, n = len(word2)+1`),
  plus O(m+n) to walk the traceback path once — dominated by the table
  fill.
- **Space:** O(m·n) — the full matrix is kept and returned, not collapsed
  to the usual O(min(m,n)) rolling-row optimization you'd use if you only
  needed the final integer. That's a deliberate tradeoff here: the whole
  point of this implementation is returning the annotated matrix and
  derived path, not just the minimal distance, so the full table has to be
  retained anyway.

This is currently the library's only dynamic-programming algorithm.
`PathToOnePointO.md` flags "one DP algorithm beyond edit distance" (0/1
knapsack or LCS) as a planned category-completeness addition, not something
implemented yet.
