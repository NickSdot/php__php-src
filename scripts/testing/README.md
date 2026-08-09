# Testing Tooling

Run them from the repository root.

## Commands

### `validate_test_coverage.php`

Validates working tree test coverage against a base revision.

```sh
php scripts/testing/validate_test_coverage.php [options] [test paths...]
```

No passed paths run the complete suite. Passed paths limit runs to matching PHPT
files. The tree uses current files, including uncommitted changes. All sources
under the repository or coverage build reported by gcov are compared unless
`--source` is passed. Tree uses current files, including uncommitted changes.

#### How it works

Before running tests, the command updates separate cached base and tree builds
using `config.nice` and enables gcov. When base refs advance or sources change
locally, the affected builds are updated.

Changed executable source lines and branch outcomes count as gained when covered
and missed when not. The result table shows gained and missed coverage. As well,
as time/memory benchmarks. Coverage reports are written to `coverage.txt`.

## Tests

The tests to test this CLI itself are not run as part of the default PHP test
suite. To test the CLI run:

```sh
php run-tests.php scripts/testing/tests/unit
php run-tests.php scripts/testing/tests/integration
```
