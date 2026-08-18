--TEST--
Git repository distinguishes working tree and committed changes
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\GitRepository;
use PHP\Testing\ProcessRunner;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('git_repository')
);

$path = $temporary->path();
$repo = "$path/repo";
$process = new ProcessRunner();

mkdir($repo);
mkdir("$repo/subdirectory");
$process->command(['git', 'init', '--quiet'], $repo);

file_put_contents("$repo/tracked", 'base');
$process->command(['git', 'add', 'tracked'], $repo);
$process->command([
    'git', '-c', 'user.name=PHP', '-c', 'user.email=php@example.com',
    'commit', '--quiet', '-m', 'base',
], $repo);

$repository = GitRepository::discover("$repo/subdirectory", $process);
$base = $repository->resolve('HEAD');

file_put_contents("$repo/tracked", 'tree');
file_put_contents("$repo/untracked", 'tree');

var_dump($repository->path() === realpath($repo));
var_dump($repository->changedPaths($base));

$process->command(['git', 'add', 'tracked', 'untracked'], $repo);
$process->command([
    'git', '-c', 'user.name=PHP', '-c', 'user.email=php@example.com',
    'commit', '--quiet', '-m', 'tree',
], $repo);

$tree = $repository->resolve('HEAD');
file_put_contents("$repo/dirty", 'working tree');

var_dump($repository->changedPaths($base, $tree));
var_dump($repository->deletedPaths($base, $tree));

$process->command(['git', 'checkout', '--quiet', '-b', 'base-advanced', $base], $repo);
file_put_contents("$repo/base-only", 'base');
$process->command(['git', 'add', 'base-only'], $repo);
$process->command([
    'git', '-c', 'user.name=PHP', '-c', 'user.email=php@example.com',
    'commit', '--quiet', '-m', 'advance base',
], $repo);

$advancedBase = $repository->resolve('HEAD');
var_dump($repository->changedPaths($advancedBase, $tree));
var_dump($repository->changedPathsSince($advancedBase, $tree));

$process->command(['git', 'checkout', '--detach', '--quiet', $tree], $repo);
file_put_contents("$repo/dirty", 'working tree');

unlink("$repo/tracked");
var_dump($repository->deletedPaths($tree));

$process->command(['git', 'add', '--update'], $repo);
$process->command([
    'git', '-c', 'user.name=PHP', '-c', 'user.email=php@example.com',
    'commit', '--quiet', '-m', 'delete',
], $repo);

$deletedTree = $repository->resolve('HEAD');
var_dump($repository->deletedPaths($tree, $deletedTree));

file_put_contents("$repo/old.phpt", 'test');

$process->command(['git', 'add', 'old.phpt'], $repo);

$process->command([
    'git', '-c', 'user.name=PHP', '-c', 'user.email=php@example.com',
    'commit', '--quiet', '-m', 'rename base',
], $repo);

$renameBase = $repository->resolve('HEAD');

$process->command(['git', 'mv', 'old.phpt', 'new.phpt'], $repo);

$process->command([
    'git', '-c', 'user.name=PHP', '-c', 'user.email=php@example.com',
    'commit', '--quiet', '-m', 'rename tree',
], $repo);

$renameTree = $repository->resolve('HEAD');

var_dump($repository->renamedPaths($renameBase, $renameTree));

$checkout = "$path/checkout";
$repository->updateWorktree($base, $checkout);
var_dump(file_get_contents("$checkout/tracked"));

$repository->updateWorktree($tree, $checkout);
var_dump(file_get_contents("$checkout/tracked"));

$temporary->remove();
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('git_repository')
);
?>
--EXPECT--
bool(true)
array(2) {
  [0]=>
  string(7) "tracked"
  [1]=>
  string(9) "untracked"
}
array(2) {
  [0]=>
  string(7) "tracked"
  [1]=>
  string(9) "untracked"
}
array(0) {
}
array(3) {
  [0]=>
  string(9) "base-only"
  [1]=>
  string(7) "tracked"
  [2]=>
  string(9) "untracked"
}
array(2) {
  [0]=>
  string(7) "tracked"
  [1]=>
  string(9) "untracked"
}
array(1) {
  [0]=>
  string(7) "tracked"
}
array(1) {
  [0]=>
  string(7) "tracked"
}
array(1) {
  ["old.phpt"]=>
  string(8) "new.phpt"
}
string(4) "base"
string(4) "tree"
