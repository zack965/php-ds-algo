# Contributing

Thanks for looking at `zack965/php-ds-algo`. This is a stable, tested,
production-ready PHP library, and outside contributions (bug fixes, new
algorithms/data structures, test coverage) are welcome via the standard
GitHub fork workflow.

## Fork & branch strategy

1. **Fork** the repo on GitHub: https://github.com/zack965/php-ds-algo/fork
2. **Clone your fork** locally and add the upstream remote:

   ```bash
   git clone https://github.com/<your-username>/php-ds-algo.git
   cd php-ds-algo
   git remote add upstream https://github.com/zack965/php-ds-algo.git
   ```

3. **Keep `main` in sync with upstream** before starting new work:

   ```bash
   git fetch upstream
   git checkout main
   git merge upstream/main
   ```

4. **Create a topic branch** off `main` — don't commit directly to `main`:

   ```bash
   git checkout -b <type>/<short-description>
   ```

   Use a prefix that matches the change: `feat/`, `fix/`, `refactor/`, `test/`,
   `docs/`. Examples: `feat/doubly-linked-list-insert-after`,
   `fix/binary-search-off-by-one`.

5. **Commit** with focused, descriptive messages. Prefer several small commits
   over one large one if the change touches multiple concerns.

6. **Push to your fork** and open a pull request against `zack965/php-ds-algo:main`:

   ```bash
   git push -u origin <type>/<short-description>
   ```

7. **Rebase, don't merge**, if `main` moves while your PR is open:

   ```bash
   git fetch upstream
   git rebase upstream/main
   git push --force-with-lease
   ```

## Before opening a PR

- Add or update PHPUnit tests under `tests/` for any behavior change.
- Run the checks locally:

  ```bash
  composer install
  composer test
  composer validate --no-check-all --strict
  find src -name '*.php' -print0 | xargs -0 -n1 php -l
  ```

- Keep PRs scoped to one logical change. Don't bundle an unrelated refactor
  with a bug fix.

## Pull request expectations

- Describe *why* the change is needed, not just what it does.
- Link any related issue.
- Be responsive to review feedback — this repo favors small, iterative PRs
  over large ones sitting unreviewed.

## Reporting bugs / proposing features

Open a GitHub issue first for anything non-trivial (a new data structure, a
breaking change) so the approach can be agreed on before you invest time in
an implementation.
