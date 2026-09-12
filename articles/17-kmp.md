# KMP: substring search without re-comparing characters you've already matched

`Zack\PhpDsAlgo\Algorithmes\Strings\KMP` implements Knuth-Morris-Pratt
substring search — the library's first (and so far only) string-matching
algorithm, distinct from the array-element searches in `ArraySearchAlogorthme`
(see `articles/09-searching-algorithms.md`). Two static methods:

```php
KMP::calculateLspTable(array $data): array   // $data: pattern, one character per element
KMP::run(string $text, string $pattern): array // zero-based starting indexes of every match
```

## The problem naive substring search has

A naive search, on a mismatch partway through comparing the pattern against
some position in the text, just slides the pattern one position to the
right and starts re-comparing from the pattern's first character again —
throwing away everything it already learned about the partial match. KMP's
whole idea is to avoid that: precompute, for every position in the
*pattern itself*, how far it could safely fall back to without ever having
to re-check characters that are guaranteed to still match, based purely on
the pattern's own internal structure (independent of whatever text it'll
eventually be run against).

## `calculateLspTable()` — the "longest suffix-prefix" table

```php
public static function calculateLspTable(array $data): array
{
    $lspTable = array_fill(0, count($data), 0);
    $prefixLength = 0;
    $i = 1;
    while ($i < count($data)) {
        $element = $data[$i];
        $prefixElement = $data[$prefixLength];
        if ($element == $prefixElement) {
            $prefixLength++;
            $lspTable[$i] = $prefixLength;
        } else {
            if ($prefixLength != 0) {
                $prefixLength = $lspTable[$prefixLength - 1];
                continue;
            } else {
                $lspTable[$i] = 0;
            }
        }
        $i++;
    }
    return $lspTable;
}
```

For each position `$i` in the pattern, `$lspTable[$i]` is the length of the
longest proper prefix of the pattern that's *also* a suffix of the
substring ending at `$i`. Concretely, for the pattern `AABA`:

| index | 0 | 1 | 2 | 3 |
|---|---|---|---|---|
| character | A | A | B | A |
| LSP value | 0 | 1 | 0 | 1 |

Index 1 (`AA`) has LSP `1` because the single-character prefix `A` is also
its suffix. Index 3 (`AABA`) has LSP `1` for the same reason (`A` prefix,
`A` suffix) — the middle `AB` breaks a longer match. This table is exactly
what lets `run()` skip ahead intelligently on a mismatch: it's the answer
to "if I've matched this many pattern characters and the next one fails,
how much of that match can I keep without re-checking anything?"

The two-pointer walk (`$i` scanning the pattern, `$prefixLength` tracking
the current candidate prefix length) is the same core loop `run()` reuses
against the text — building the LSP table is really "running KMP with the
pattern searching against itself."

## `run()` — the actual search

```php
public static function run(string $text, string $pattern): array
{
    if ($pattern === '') {
        return [];
    }
    $str_array = str_split($text);
    $pattenr_array = str_split($pattern);
    $pattenr_array_count = count($pattenr_array);
    if (empty($str_array)) {
        return [];
    }
    $indexes = [];
    $lspTable = self::calculateLspTable($pattenr_array);
    $i = 0; // loop over the string
    $j = 0; // loop over LPS table
    while ($i < count($str_array)) {
        if ($str_array[$i] == $pattenr_array[$j]) {
            $j++;
            $i++;
            if ($j == $pattenr_array_count) {
                $indexes[] = $i - $j;
                $j = $lspTable[$j - 1];
            }
        } else {
            if ($j != 0) {
                $j = $lspTable[$j - 1];
            } else {
                $i++;
            }
        }
    }
    return $indexes;
}
```

Two pointers: `$i` walks the text, `$j` walks the pattern (and doubles as
"how many pattern characters are currently matched"). On a match, both
advance; if `$j` reaches the full pattern length, a match starting at `$i -
$j` is recorded, and `$j` falls back to `$lspTable[$j - 1]` — *not* to `0`
— so overlapping occurrences (see `AABA` in `AABAABA` below) are still
found rather than skipped. On a mismatch, `$j` falls back via the LSP
table (reusing however much of the already-matched prefix is still valid)
without ever moving `$i` backward — the text pointer only ever advances,
which is the source of KMP's linear-time guarantee. If `$j` is already `0`
(no partial match to fall back from), `$i` just advances by one, same as
the naive approach would in that specific case.

```php
KMP::run('ABABDABACDABABCABAB', 'ABABCABAB'); // [10]
KMP::run('AABAACAADAABAABA', 'AABA');          // [0, 9, 12] — overlapping matches included
KMP::run('hello', '');                          // [] — empty pattern short-circuits immediately
KMP::run('', 'hello');                          // [] — empty text short-circuits too
```

An empty `$pattern` returns `[]` immediately (there's no meaningful
"empty pattern matches everywhere" behavior implemented); an empty `$text`
falls through to the `empty($str_array)` check and also returns `[]`.
Overlapping matches are always included — `run('AAAAA', 'AA')` returns
`[0, 1, 2, 3]`, one for every valid starting position, not just
non-overlapping ones.

## Complexity summary

| Operation | Time | Space | Notes |
|---|---|---|---|
| `calculateLspTable` | O(m) | O(m) | m = pattern length; run once per `run()` call |
| `run` | O(n + m) | O(n + m) | n = text length; `str_split()` on both plus the LSP table account for the space |

The whole point relative to a naive O(n·m) search: the text pointer `$i`
never moves backward, and every fallback on a mismatch is an O(1) table
lookup rather than a re-scan — so the total work across the whole search
is bounded by `n + m`, not `n * m`.
