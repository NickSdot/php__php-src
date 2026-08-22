--TEST--
PHPT scope selects files and complete directories
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\PhptScope;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('phpt_scope')
);

$root = $temporary->path();

mkdir($root . '/tests');
mkdir($root . '/tests/nested');

file_put_contents($root . '/tests/first.phpt', '');
file_put_contents($root . '/tests/nested/second.phpt', '');
file_put_contents($root . '/tests/ignored.txt', '');
file_put_contents($root . '/tests/.hidden.phpt', '');

$scope = new PhptScope();

foreach ($scope->files($root, ['tests']) as $file) {
    echo substr($file, strlen($root) + 1), "\n";
}

try {
    $scope->files($root, ['tests/ignored.txt']);
} catch (Throwable $e) {
	echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('phpt_scope')
);

?>
--EXPECT--
tests/first.phpt
tests/nested/second.phpt
InvalidArgumentException: Path does not match a PHPT file: tests/ignored.txt
