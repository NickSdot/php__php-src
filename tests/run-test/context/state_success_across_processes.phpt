--TEST--
Test TestContext persistence - anonymous counters continue across separate FILE processes
--FILE--
<?php

require dirname(__DIR__, 2) . '/harness/php-process.inc.php';

$script = Workspace::file('allocate.php');

file_put_contents($script->path, <<<'PHP'
    <?php

    echo Workspace::file()->name, "\n";
    PHP);

$arguments = [
    '-n',
    '-d',
    'auto_prepend_file=' . ini_get('auto_prepend_file'),
];

[$firstExitCode, $firstOutput] = run_php_process($script->path, $arguments);
[$secondExitCode, $secondOutput] = run_php_process($script->path, $arguments);

var_dump($firstExitCode);
var_dump(trim($firstOutput));
var_dump($secondExitCode);
var_dump(trim($secondOutput));

?>
--EXPECT--
int(0)
string(6) "file-1"
int(0)
string(6) "file-2"
