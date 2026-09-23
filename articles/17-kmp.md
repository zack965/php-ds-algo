# KMP Substring Search

**Namespace:** `Zack\PhpDsAlgo\Algorithmes\Strings`
**Class:** `KMP`

## What it is

**Knuth-Morris-Pratt (KMP)** finds every occurrence of a **pattern** inside
a **text** in **linear time**.

A simple substring search restarts the pattern from scratch after each
mismatch. KMP instead uses what it has already matched: it precomputes a
table from the pattern, and on a mismatch it jumps straight to the longest
partial match it can reuse. The pointer into the text **never moves
backward**.

```php
use Zack\PhpDsAlgo\Algorithmes\Strings\KMP;

KMP::run('ABABDABACDABABCABAB', 'ABABCABAB'); // [10]
KMP::run('AABAACAADAABAABA', 'AABA');          // [0, 9, 12]
KMP::run('AAAAA', 'AA');                       // [0, 1, 2, 3], overlapping matches included
```

## API

```php
KMP::run(string $text, string $pattern): array;   // zero-based start index of every match
KMP::calculateLspTable(array $pattern): array;     // pattern as an array of characters
```

- `run()` returns **every** match, **including overlapping** ones, in order.
- An empty pattern or an empty text returns `[]`.
- `calculateLspTable()` is public, so you can inspect or reuse the
  precomputed table on its own.

## How it works

### Step 1: the LSP table

For each position `i` in the pattern, the **LSP** (Longest proper prefix
that is also a Suffix) table stores the length of the longest prefix of the
pattern that also ends at position `i`.

For the pattern `AABA`:

| index | 0 | 1 | 2 | 3 |
|---|---|---|---|---|
| character | A | A | B | A |
| LSP | 0 | 1 | 0 | 1 |

```php
KMP::calculateLspTable(str_split('AABA')); // [0, 1, 0, 1]
```

The table answers one question: *"I have matched this much of the pattern
and the next character failed. How much of the match can I keep?"*

### Step 2: the search

Two pointers move forward:

- `i` walks the **text**.
- `j` counts how many pattern characters are currently matched.

1. **Characters match:** advance both. If `j` reaches the pattern length,
   record a match at `i − j`, then set `j = LSP[j − 1]` so overlapping matches
   are still found.
2. **Mismatch with `j > 0`:** set `j = LSP[j − 1]`, which reuses the longest
   valid partial match. `i` stays where it is.
3. **Mismatch with `j = 0`:** advance `i`.

Because `i` only moves forward and each fallback is a single table lookup,
the total work is proportional to the length of the text plus the length of
the pattern.

## Complexity

n = text length, m = pattern length.

| Operation | Time | Space |
|---|---|---|
| `calculateLspTable` | O(m) | O(m) |
| `run` | O(n + m) | O(n + m) |

## When to use it

- **Finding every occurrence** of a word or phrase in a document,
  including overlapping ones.
- **Log and stream scanning:** look for signatures or markers in large
  inputs with predictable, linear performance.
- **Patterns with repeated structure** such as `AAAB`, `ABABAB` or DNA
  motifs, where simple search would re-compare the same characters many
  times.
- **Bioinformatics:** locating short motifs in long genetic sequences.
- **Plagiarism and duplicate detection** building blocks.
- **Learning string algorithms:** the LSP table is a classic idea in
  preprocessing.

## When to choose something else

- **Only checking whether a substring appears once:** PHP's built-in
  `str_contains()` or `strpos()` answer that directly.
- **Approximate matches that tolerate typos:** use
  [Levenshtein Distance](11-levenshtein-distance.md).
- **Complex patterns** (wildcards, alternatives, character classes): use
  regular expressions (`preg_match_all`).
