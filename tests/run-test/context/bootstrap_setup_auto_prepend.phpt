--TEST--
SETUP bootstrap invokes the runner auto_prepend_file after loading TestContext
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/run-tests.inc.php';

$testFile = TestPath::file('nested.phpt');
$prependFile = TestPath::file('prepend.php');
$setupRecord = TestPath::file('setup.txt');

file_put_contents($prependFile, <<<'PHP'
    <?php

    if (str_ends_with($_SERVER['SCRIPT_FILENAME'], '.setup.php')) {
        if (!class_exists(TestContext::class, false)) {
            throw new Exception('TestContext is unavailable in auto_prepend_file');
        }

        if (class_exists('TestPath', false)) {
            throw new Exception('TestPath is available in SETUP auto_prepend_file');
        }

        file_put_contents(getenv('PHP_TEST_SETUP_RECORD'), 'setup');
    }
    PHP);

file_put_contents($testFile, <<<'PHPT'
    --TEST--
    Nested SETUP auto prepend test
    --SETUP--
    <?php

    TestContext::useRelativeWorkspace();

    ?>
    --FILE--
    <?php

    var_dump(file_get_contents(getenv('PHP_TEST_SETUP_RECORD')));

    ?>
    --EXPECT--
    string(5) "setup"
    PHPT);

[$exitCode, $output] = run_test_harness(
    $testFile->path,
    options: ['-d', "auto_prepend_file=$prependFile->path"],
    environment: ['PHP_TEST_SETUP_RECORD' => $setupRecord->path],
);

var_dump($exitCode);
var_dump(str_contains($output, 'PASS'));

?>
--EXPECT--
int(0)
bool(true)
