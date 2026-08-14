--TEST--
Test coverage command retains canonical scanf coverage
--CONFLICTS--
all
--ENV--
TEST_TIMEOUT=600
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

const BASE = 'c27368c9392c46047c20d86f536753c29e73b8a0';
const TREE = '072039845191d06ee7b72284bdeb763f563d7018';

$repo = dirname(__DIR__, 4);
$workspace = IntegrationTestWorkspace::create(
    $repo,
    TREE,
    TestTemporaryDirectory::stateFile('validate_test_coverage_scanf')
);

$workspace->configure(['--disable-all']);

$fixture = $workspace->path();
$process = new ProcessRunner();

$command = [
    PHP_BINARY,
    "$repo/scripts/testing/validate_test_coverage.php",
    '--base', BASE,
    '--tree', 'HEAD',
    '--source', 'ext/standard/scanf.c',
    'ext/standard/tests/file',
    'ext/standard/tests/strings',
];
$environment = getenv();
$environment['CIRCLECI'] = '1';
$environment['NO_COLOR'] = '1';
$environment['TMPDIR'] = $workspace->temporaryPath();
$environment['TMP'] = $workspace->temporaryPath();
$environment['TEMP'] = $workspace->temporaryPath();
unset($environment['PATH_TRANSLATED'], $environment['QUERY_STRING'], $environment['REQUEST_METHOD'], $environment['SCRIPT_FILENAME'], $environment['TEST_PHP_EXECUTABLE']);

$stream = tmpfile();

if ($stream === false) {
    throw new RuntimeException('Could not create output stream');
}

$status = $process->process($command, $fixture, $environment, $stream, $stream);

rewind($stream);
$output = stream_get_contents($stream);

if ($output === false) {
    throw new RuntimeException('Could not read coverage output');
}

echo $output;

if ($status !== 0) {
    throw new RuntimeException("Coverage command failed with status $status");
}

if (preg_match('/^Report: (.+)$/m', $output, $matches) !== 1) {
    throw new RuntimeException('Coverage output does not contain report path');
}

$report = file_get_contents($matches[1]);

foreach ([
    '===== Missed ' . str_repeat('=', 67) . "\n\n      Lines (0)\n      " . str_repeat('-', 74) . "\n      None",
    "      Branches (0)\n      " . str_repeat('-', 74) . "\n      None",
    "      ext/standard/scanf.c:\n",
] as $expected) {
    if (str_contains($report, $expected) === false) {
        throw new RuntimeException("Coverage report does not contain: $expected");
    }
}

if (preg_match('/(?:^        | )156(?: |$)/m', $report) !== 1) {
    throw new RuntimeException('Coverage report does not contain line 156');
}
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';
require dirname(__DIR__) . '/IntegrationTestWorkspace.inc.php';

PHP\Testing\IntegrationTestWorkspace::remove(
    dirname(__DIR__, 4),
    PHP\Testing\TestTemporaryDirectory::stateFile('validate_test_coverage_scanf'),
);
?>
--EXPECTF--
Base: c27368c9392c46047c20d86f536753c29e73b8a0 c27368c9392c46047c20d86f536753c29e73b8a0
Tree: 072039845191d06ee7b72284bdeb763f563d7018 HEAD
Building tree
Coverage: ext/standard/scanf.c
Running base 1624 tests
Running tree 1583 tests
%r[+](?:-+[+]){7}%r
|%w|%wTests%w|%wSources%w|%wLines%w|%wBranches%w|%wTime%w|%wMemory%w|
%r[+](?:-+[+]){7}%r
| Base%w|%w1624 |%w1 | 439/537 (81.75%) | 296/376 (78.72%) |%w%fs |%w%f MB |
| Tree%w|%w1583 |%w1 | 524/537 (97.58%) | 362/376 (96.28%) |%w%fs |%w%f MB |
| Change%w|%w-41 |%w0 |%w+85 / -0%w|%w+66 / -0%w|%w%fs |%w%f MB |
%r[+](?:-+[+]){7}%r
Report: %scoverage.txt
PASS
