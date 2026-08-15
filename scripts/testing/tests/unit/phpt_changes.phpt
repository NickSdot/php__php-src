--TEST--
PHPT changes detect created, deleted, renamed and skipped tests
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\PhptChange;
use PHP\Testing\PhptChanges;
use PHP\Testing\PhptResults;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('phpt_changes')
);

$base = $temporary->path() . '/base';
$tree = $temporary->path() . '/tree';

mkdir($base);
mkdir($tree);

foreach ([
    "$base/deleted.phpt" => 'deleted',
    "$base/duplicate-one.phpt" => 'duplicate',
    "$base/duplicate-two.phpt" => 'duplicate',
    "$base/old-exact.phpt" => 'exact',
    "$base/old-git.phpt" => 'old',
    "$tree/created.phpt" => 'created',
    "$tree/duplicate-new.phpt" => 'duplicate',
    "$tree/new-exact.phpt" => 'exact',
    "$tree/new-git.phpt" => 'new',
] as $file => $contents) {
    file_put_contents($file, $contents);
}

$baseResults = new PhptResults([
    'deleted.phpt' => 'PASS',
    'duplicate-one.phpt' => 'PASS',
    'duplicate-two.phpt' => 'PASS',
    'newly-skipped.phpt' => 'PASS',
    'no-longer-skipped.phpt' => 'SKIP',
    'old-exact.phpt' => 'SKIP',
    'old-git.phpt' => 'PASS',
    'still-skipped.phpt' => 'SKIP',
]);

$treeResults = new PhptResults([
    'created.phpt' => 'SKIP',
    'duplicate-new.phpt' => 'PASS',
    'new-exact.phpt' => 'SKIP',
    'new-git.phpt' => 'SKIP',
    'newly-skipped.phpt' => 'SKIP',
    'no-longer-skipped.phpt' => 'PASS',
    'still-skipped.phpt' => 'SKIP',
]);

$changes = PhptChanges::between(
    $baseResults,
    $treeResults,
    ['old-git.phpt' => 'new-git.phpt'],
    $base,
    $tree
);

function show(string $name, array $changes): void
{
    echo "$name\n";

    foreach ($changes as $change) {
        assert($change instanceof PhptChange);

        printf(
            "%s | %s | %s -> %s\n",
            $change->basePath ?? '-',
            $change->treePath ?? '-',
            $change->baseStatus ?? '-',
            $change->treeStatus ?? '-'
        );
    }
}

show('Created', $changes->created());
show('Deleted', $changes->deleted());
show('Renamed', $changes->renamed());
show('Skipped', $changes->skipped());

$temporary->remove();
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('phpt_changes')
);
?>
--EXPECT--
Created
- | created.phpt | - -> SKIP
- | duplicate-new.phpt | - -> PASS
Deleted
deleted.phpt | - | PASS -> -
duplicate-one.phpt | - | PASS -> -
duplicate-two.phpt | - | PASS -> -
Renamed
old-exact.phpt | new-exact.phpt | SKIP -> SKIP
old-git.phpt | new-git.phpt | PASS -> SKIP
Skipped
- | created.phpt | - -> SKIP
old-git.phpt | new-git.phpt | PASS -> SKIP
newly-skipped.phpt | newly-skipped.phpt | PASS -> SKIP
old-exact.phpt | new-exact.phpt | SKIP -> SKIP
still-skipped.phpt | still-skipped.phpt | SKIP -> SKIP
no-longer-skipped.phpt | no-longer-skipped.phpt | SKIP -> PASS
