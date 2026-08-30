--TEST--
Using exit/die as a statement/constant
--FILE--
<?php
TestContext::useRelativeWorkspace();

$file = TestPath::file('exit.php');
const FILE_CONTENT = <<<'TEMPLATE'
<?php
echo "Before FUNCTION";
try {
    FUNCTION;
} catch (\Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

TEMPLATE;


$php = getenv('TEST_PHP_EXECUTABLE_ESCAPED');
$command = $php . ' ' . escapeshellarg($file->path);

foreach (['exit', 'die'] as $value) {
    echo 'Using ', $value, ' as value:', PHP_EOL;
    $output = [];
    $content = str_replace('FUNCTION', $value, FILE_CONTENT);
    file_put_contents($file, $content);
    exec($command, $output, $exit_status);
    echo 'Exit status is: ', $exit_status, PHP_EOL,
         'Output is:', PHP_EOL, join($output), PHP_EOL;
}

?>
--EXPECT--
Using exit as value:
Exit status is: 0
Output is:
Before exit
Using die as value:
Exit status is: 0
Output is:
Before die
