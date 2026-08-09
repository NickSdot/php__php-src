--TEST--
Build configuration fingerprint ignores tests and tracks build inputs
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\BuildConfiguration;
use PHP\Testing\GitRepository;
use PHP\Testing\ProcessRunner;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('build_configuration')
);

$source = $temporary->path();
$process = new ProcessRunner();

$process->command(['git', 'init', '--quiet'], $source);

mkdir("$source/ext/example/tests", recursive: true);
file_put_contents("$source/configure.ac", 'configure');
file_put_contents("$source/ext/example/config.m4", 'config');
file_put_contents("$source/ext/example/example.c", 'source');
file_put_contents("$source/ext/example/tests/example.phpt", 'test');

$configuration = new BuildConfiguration(new GitRepository($source, $process));
$initial = $configuration->fingerprint($source, 'options');

file_put_contents("$source/ext/example/tests/example.phpt", 'changed test');
file_put_contents("$source/ext/example/example.c", 'changed source');
var_dump($configuration->fingerprint($source, 'options') === $initial);

file_put_contents("$source/ext/example/config.m4", 'changed config');
var_dump($configuration->fingerprint($source, 'options') !== $initial);
var_dump($configuration->fingerprint($source, 'different options') !== $initial);
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('build_configuration')
);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
