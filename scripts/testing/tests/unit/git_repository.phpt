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
string(4) "base"
string(4) "tree"
