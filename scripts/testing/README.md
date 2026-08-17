# Testing Tooling

Run them from the repository root.

## Commands

### `validate_test_coverage.php`

Validates tree test coverage against a base revision. The current directory
determines the repository.

```sh
php scripts/testing/validate_test_coverage.php [options] [test paths...]
```

No passed paths run the complete suite and compare all discovered sources. Passed
paths limit runs to matching PHPT files and compare their components plus changed
source components. `--source` limits the source scope and `--global` compares all
discovered sources, including vendored sources. Known vendor sources are excluded
unless changed or explicitly selected via `--source`. The tree uses current files
including uncommitted changes; `--tree` selects a branch/commit. CLI builds use
`config.nice` with `gcov` and `zend_test` automatically appended.

## Tests

The tests to test this CLI itself are not run as part of the default PHP test
suite. To test the CLI run:

```sh
php run-tests.php scripts/testing/tests/unit
php run-tests.php scripts/testing/tests/integration
```
