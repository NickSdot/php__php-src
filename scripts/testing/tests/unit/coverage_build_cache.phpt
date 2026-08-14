--TEST--
Coverage build cache reuses and separates build configurations
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\CoverageBuildCache;

$firstKey = CoverageBuildCache::key('/repo', 'master', 'configuration');
$sameKey = CoverageBuildCache::key('/repo', 'master', 'configuration');
$otherReference = CoverageBuildCache::key('/repo', 'PHP-8.4', 'configuration');
$otherConfiguration = CoverageBuildCache::key('/repo', 'master', 'other configuration');

var_dump($firstKey === $sameKey);
var_dump($firstKey !== $otherReference);
var_dump($firstKey !== $otherConfiguration);

$first = new CoverageBuildCache($firstKey);
$other = new CoverageBuildCache($otherConfiguration);
$initialisations = 0;

$directory = $first->directory(function (string $directory) use (&$initialisations): void {
    $initialisations++;
    file_put_contents("$directory/build", 'first');
});

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

(new CoverageBuildCache(CoverageBuildCache::key('/repo', 'master', 'configuration')))->remove();
(new CoverageBuildCache(CoverageBuildCache::key('/repo', 'master', 'other configuration')))->remove();
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
int(1)
int(2)
NULL
NULL
