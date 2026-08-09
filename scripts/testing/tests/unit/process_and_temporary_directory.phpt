--TEST--
Measured processes and guarded temporary directories
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\ProcessRunner;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('process_and_temporary_directory')
);

$directory = $temporary->path();

$result = (new ProcessRunner())->measured(
    [PHP_BINARY, '-r', 'fwrite(STDOUT, "output"); exit(3);'],
    $directory,
    getenv(),
    $directory . '/stdout',
    $directory . '/stderr',
    $directory . '/metrics.json',
);

var_dump($result->status);
var_dump($result->time >= 0.0);
var_dump($result->memory === null || is_int($result->memory));
var_dump(file_get_contents($directory . '/stdout'));

$output = (new ProcessRunner())->command([
    PHP_BINARY,
    '-r',
    'fwrite(STDERR, str_repeat("error", 200000)); fwrite(STDOUT, "done");',
]);

var_dump($output);

$temporary->remove();

var_dump(file_exists($directory));
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('process_and_temporary_directory')
);

?>
--EXPECT--
int(3)
bool(true)
bool(true)
string(6) "output"
string(4) "done"
bool(false)
