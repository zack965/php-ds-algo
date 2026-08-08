# Changelog

One file per released version, generated from git tags and history. Format
loosely follows [Keep a Changelog](https://keepachangelog.com/) — grouped
into Added / Changed / Fixed where the commit history makes that distinction
clear, plus the raw commit list for each release for traceability.

| Version | Date | Summary |
|---|---|---|
| [v0.2.0](v0.2.0.md) | 2026-08-05 | Linear/ternary/Fibonacci search, directed-graph cycle detection, adjacency matrix support. |
| [v0.1.1](v0.1.1.md) | 2026-08-04 | Merge sort, quick sort, Levenshtein distance, `CONTRIBUTING.md`. |
| [v0.1.0](v0.1.0.md) | 2026-07-21 | Initial release — linked lists, stack, queue, graph, BFS/DFS, bubble/selection/insertion sort, search algorithms, sliding window. |

Unreleased: nothing on `main` past `v0.2.0` (`74aecb3`) as of 2026-08-08.

Regenerate/extend this by walking tags in creation order and diffing each
against the previous one:

```bash
git tag --sort=creatordate
git log --reverse --format='%h %ad %s' --date=short <prev-tag>..<tag>
git diff --stat <prev-tag> <tag>
```
