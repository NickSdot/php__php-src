--TEST--
Test coverage command handles C-only and mixed changes
--CONFLICTS--
all
--ENV--
TEST_TIMEOUT=1200
--INI--
max_execution_time=0
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';
require dirname(__DIR__) . '/IntegrationTestWorkspace.inc.php';

use PHP\Testing\IntegrationTestWorkspace;
use PHP\Testing\ProcessRunner;
use PHP\Testing\TestTemporaryDirectory;

const BASE = 'c8682fb61af207c09c71bcded7fe5ba7b17d4a5e';
const TREE = '84d6093982431f7cfa8588c13c0c8a37f545d3cb';

/**
 * @param list<string> $tests
 * @return array{int, string}
 */
function runCoverage(ProcessRunner $process, string $commandPath, string $fixture, string $temporary, string $name, array $tests): array
{
    $command = [
        PHP_BINARY,
        $commandPath,
        '--base', BASE,
        '--source', 'ext/standard/string.c',
        ...$tests,
    ];

    $environment = getenv();
    $environment['CIRCLECI'] = '1';
    $environment['NO_COLOR'] = '1';
    $environment['TMPDIR'] = $temporary;
    $environment['TMP'] = $temporary;
    $environment['TEMP'] = $temporary;
    unset($environment['PATH_TRANSLATED'], $environment['QUERY_STRING'], $environment['REQUEST_METHOD'], $environment['SCRIPT_FILENAME'], $environment['TEST_PHP_EXECUTABLE']);

    $stdout = "$temporary/$name.out";
    $stderr = "$temporary/$name.err";
    $status = $process->process($command, $fixture, $environment, $stdout, $stderr);
    $output = file_get_contents($stdout) . file_get_contents($stderr);

    unlink($stdout);
    unlink($stderr);

    return [$status, $output];
}

/** @param list<string> $expected */
function assertCoverage(string $name, int $actualStatus, int $expectedStatus, string $output, array $expected): void
{
    if ($actualStatus !== $expectedStatus) {
        throw new RuntimeException("$name returned $actualStatus instead of $expectedStatus\n$output");
    }

    foreach ($expected as $text) {
        if (str_contains($output, $text) === false) {
            throw new RuntimeException("$name output does not contain: $text\n$output");
        }
    }

    echo "$name: OK\n";
}

function coverageReport(string $output): string
{
    if (preg_match('/^Report: (.+)$/m', $output, $matches) !== 1) {
        throw new RuntimeException('Coverage output does not contain report path');
    }

    return $matches[1];
}

$repo = dirname(__DIR__, 4);
$workspace = IntegrationTestWorkspace::create(
    $repo,
    TREE,
    TestTemporaryDirectory::stateFile('validate_test_coverage_source_changes')
);

$workspace->configure(['--disable-all']);

$fixture = $workspace->path();
$temporary = $workspace->temporaryPath();
$process = new ProcessRunner();
$commandPath = "$repo/scripts/testing/validate_test_coverage.php";

[$status, $output] = runCoverage(
    $process,
    $commandPath,
    $fixture,
    $temporary,
    'c-only',
    ['ext/standard/tests/strings']
);

assertCoverage('C-only', $status, 0, $output, ['| Base   |   735 |', 'Coverage: ext/standard/string.c', '+10 / -0', '+6 / -0', 'PASS']);

[$status, $output] = runCoverage(
    $process,
    $commandPath,
    $fixture,
    $temporary,
    'negative',
    ['ext/standard/tests/strings/strlen.phpt']
);
assertCoverage('Negative', $status, 1, $output, ['| Base   |     1 |', 'FAIL']);

$report = file_get_contents(coverageReport($output));

if (str_contains($report, "      ext/standard/string.c:\n") === false) {
    throw new RuntimeException('Negative report does not contain ext/standard/string.c');
}

foreach (['857', '866:0'] as $location) {
    if (preg_match('/(?:^        | )' . preg_quote($location, '/') . '(?: |$)/m', $report) !== 1) {
        throw new RuntimeException("Negative report does not contain: $location");
    }
}

$mixedTest = "$fixture/ext/standard/tests/strings/coverage_validator_mixed.phpt";
file_put_contents($mixedTest, <<<'PHPT'
    --TEST--
    Coverage validator mixed fixture
    --FILE--
    <?php echo "mixed\n"; ?>
    --EXPECT--
    mixed
    PHPT);

[$status, $output] = runCoverage(
    $process,
    $commandPath,
    $fixture,
    $temporary,
    'mixed',
    ['ext/standard/tests/strings']
);
assertCoverage('Mixed', $status, 0, $output, ['| Base   |   735 |', '| Tree   |   736 |', '+10 / -0', '+6 / -0', 'PASS']);
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';
require dirname(__DIR__) . '/IntegrationTestWorkspace.inc.php';

PHP\Testing\IntegrationTestWorkspace::remove(
    dirname(__DIR__, 4),
    PHP\Testing\TestTemporaryDirectory::stateFile('validate_test_coverage_source_changes')
);
?>
--EXPECT--
C-only: OK
Negative: OK
Mixed: OK
