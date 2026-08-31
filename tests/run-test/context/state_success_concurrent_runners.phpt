--TEST--
Test RunnerContext identity - overlapping runner processes isolate the same test
--FILE--
<?php

use TestSuite\Context\TestContext;

$testFile = Workspace::file('nested.phpt')->create();
$script = Workspace::file('runner.php');

file_put_contents($script, <<<'PHP'
    <?php

    require $argv[1] . '/RunnerContext.php';
    require $argv[1] . '/Workspace.php';

    $runner = TestSuite\Context\RunnerContext::instance(preserve: false);
    $context = $runner->startTest($argv[2]);
    putenv('PHP_TEST_CONTEXT=' . $context->stateFile);
    Workspace::file('fixture')->create();

    echo $context->stateFile, "\n";
    fflush(STDOUT);
    fgets(STDIN);

    $runner->finishTest($context);
    PHP);

$runners = [];
$contexts = [];

for ($i = 0; $i < 2; $i++) {
    $process = proc_open(
        [
            getenv('TEST_PHP_EXECUTABLE'),
            '-n',
            $script->path,
            dirname(__DIR__, 3) . '/testing/Context',
            $testFile->path,
        ],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['redirect', 1]],
        $pipes,
    );
    $contexts[] = TestContext::load(trim(fgets($pipes[1])));
    $runners[] = [$process, $pipes];
}

echo "While both runners are active:\n";
var_dump($contexts[0]->stateFile !== $contexts[1]->stateFile);
var_dump($contexts[0]->workspaceDirectory !== $contexts[1]->workspaceDirectory);
foreach ($contexts as $context) {
    var_dump(is_file($context->stateFile), is_file($context->workspaceDirectory . '/fixture'));
}

echo "After finishing:\n";
foreach ($runners as [$process, $pipes]) {
    fwrite($pipes[0], "finish\n");
    fclose($pipes[0]);
    echo stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    var_dump(proc_close($process));
}
foreach ($contexts as $context) {
    var_dump(is_file($context->stateFile), is_dir($context->workspaceDirectory));
}

?>
--EXPECT--
While both runners are active:
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
After finishing:
int(0)
int(0)
bool(false)
bool(false)
bool(false)
bool(false)
