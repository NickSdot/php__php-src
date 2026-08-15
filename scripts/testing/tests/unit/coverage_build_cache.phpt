--TEST--
Coverage build cache reuses roles and separates repositories and configurations
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\CoverageBuildCache;
use PHP\Testing\Storage;

$firstKey = CoverageBuildCache::key('/repo', 'tree', 'configuration');
$sameKey = CoverageBuildCache::key('/repo', 'tree', 'configuration');
$otherRole = CoverageBuildCache::key('/repo', 'base', 'configuration');
$otherRepository = CoverageBuildCache::key('/other-repo', 'tree', 'configuration');
$otherConfiguration = CoverageBuildCache::key('/repo', 'tree', 'other configuration');

var_dump($firstKey === $sameKey);
var_dump($firstKey !== $otherRole);
var_dump($firstKey !== $otherRepository);
var_dump($firstKey !== $otherConfiguration);

$first = new CoverageBuildCache($firstKey);
$other = new CoverageBuildCache($otherConfiguration);
$initialisations = 0;

$directory = $first->directory(function (string $directory) use (&$initialisations): void {
    $initialisations++;
    file_put_contents("$directory/build", 'first');
});

var_dump(dirname($directory) === Storage::builds());

var_dump($first->directory(function () use (&$initialisations): void {
    $initialisations++;
}) === $directory);
var_dump($initialisations);

$other->directory(function (string $directory) use (&$initialisations): void {
    $initialisations++;
    file_put_contents("$directory/build", 'other');
});

var_dump($initialisations);
var_dump($first->remove());
var_dump($other->remove());
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\CoverageBuildCache;

(new CoverageBuildCache(CoverageBuildCache::key('/repo', 'tree', 'configuration')))->remove();
(new CoverageBuildCache(CoverageBuildCache::key('/repo', 'tree', 'other configuration')))->remove();
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
int(1)
int(2)
NULL
NULL
