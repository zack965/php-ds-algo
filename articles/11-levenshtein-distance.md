# Levenshtein (Edit) Distance

**Namespace:** `Zack\PhpDsAlgo\Algorithmes`
**Class:** `LevenshteinDistance`

## What it is

The **Levenshtein distance** between two strings is the minimum number of
single-character edits needed to turn one into the other. Three edits are
allowed:

- **Insert** a character
- **Delete** a character
- **Substitute** one character for another

For example, `kitten` → `sitting` has distance **3**: substitute k→s,
substitute e→i, insert g.

`LevenshteinDistance::calculate()` returns more than the number. It also
returns the **full dynamic-programming matrix** and the **step-by-step edit
path**, so you can show *how* one word becomes the other.

```php
use Zack\PhpDsAlgo\Algorithmes\LevenshteinDistance;

$result = LevenshteinDistance::calculate('kitten', 'sitting');

$result['minimumEditDistance']; // 3
$result['path'];                // list of edit steps
$result['matrix'];              // annotated DP table
$result['WordsData'];           // word lengths and padded words used to build the table
```

## The result

| Key | Contents |
|---|---|
| `minimumEditDistance` | The edit distance, as an `int` |
| `path` | The sequence of operations that achieves the distance |
| `matrix` | The DP table. Row 0 and column 0 hold the characters of the two words, and the cells hold the distances |
| `WordsData` | Metadata: row and column counts, and the padded words |

Each entry in `path` looks like this:

```php
['step' => 'Substitute', 'from' => 'e', 'to' => 'i', 'direction' => 'Top-Left']
```

`step` is one of `Match`, `Substitute`, `Insert` or `Delete`. The path is
produced by tracing back from the end of both words, so the first entry
describes the last characters. Call `array_reverse($result['path'])` to
read it from the start of the words.

## How it works

1. **Build a table.** Each cell `(i, j)` holds the distance between the first
   `i` characters of word 1 and the first `j` characters of word 2. The first
   row and column are the distances from an empty string: 0, 1, 2, …
2. **Fill every cell** with the standard recurrence:

   ```
   cell = min(
       diagonal + (0 if the characters match, else 1),  // match / substitute
       up   + 1,                                        // delete
       left + 1                                         // insert
   )
   ```
3. **Read the answer** from the bottom-right cell.
4. **Trace back** from the bottom-right cell to the top-left. At each cell,
   the neighbor that produced its value shows which edit was made. These
   steps make up the `path`.

## Complexity

With m and n the lengths of the two words:

- **Time:** O(m · n) to fill the table, plus O(m + n) to trace the path.
- **Space:** O(m · n). The full table is kept so it can be returned and used
  to rebuild the edit path.

## When to use it

- **Spell checking and "did you mean…?"** suggestions: rank dictionary words
  by distance to the typed word.
- **Fuzzy search and matching:** find records despite typos, such as
  customer names or product titles.
- **Data cleaning and deduplication:** detect near-duplicate entries.
- **Diff-style explanations:** use `path` to show exactly which characters
  changed, for teaching or for UI highlights.
- **DNA and sequence comparison,** and other problems about how similar two
  sequences are.
- **Teaching dynamic programming:** `matrix` gives you the filled table for
  display.

## When to choose something else

- **Exact substring search:** use [KMP](17-kmp.md). It finds every
  occurrence of a pattern in linear time.
- **Very long texts** (whole documents): the table grows with the product of
  the two lengths. Compare at the level of lines or tokens, or use a
  line-based diff tool.
- **Checking only whether two strings are equal:** use `===`.
