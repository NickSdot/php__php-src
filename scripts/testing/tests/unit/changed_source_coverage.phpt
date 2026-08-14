--TEST--
Changed source coverage compares unchanged locations and checks new code
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\CoverageComparator;
use PHP\Testing\CoverageSnapshot;
use PHP\Testing\GitRepository;
use PHP\Testing\ProcessRunner;
use PHP\Testing\SourceCoverage;
use PHP\Testing\SourceFileChanges;
use PHP\Testing\TestTemporaryDirectory;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('changed_source_coverage')
);

$directory = $temporary->path();
$baseSource = "$directory/base";
$treeSource = "$directory/tree";

$baseBuild = "$directory/base-build";
$treeBuild = "$directory/tree-build";
$repository = new GitRepository(dirname(__DIR__, 4), new ProcessRunner());

mkdir("$baseSource/example", recursive: true);
mkdir("$treeSource/example", recursive: true);
mkdir("$baseBuild/generated", recursive: true);
mkdir("$treeBuild/generated", recursive: true);

file_put_contents("$baseSource/example/source.c", "same\nold\nsame\nlast\n");
file_put_contents("$treeSource/example/source.c", "same\nnew uncovered\nnew covered\nsame\nlast\n");
file_put_contents("$baseBuild/generated/source.c", "same\nold\n");
file_put_contents("$treeBuild/generated/source.c", "same\nnew\nold\n");

$base = new CoverageSnapshot([
    'example/source.c' => new SourceCoverage(
        coveredLines: [1, 2, 3, 4],
        executableLines: [1, 2, 3, 4],
        coveredBranches: ['2:0', '4:0'],
        executableBranches: ['2:0', '4:0']
    ),
]);

$treeCoverage = new CoverageSnapshot([
    'example/source.c' => new SourceCoverage(
        coveredLines: [1, 3, 4],
        executableLines: [1, 2, 3, 4, 5],
        coveredBranches: ['3:0'],
        executableBranches: ['2:0', '3:0']
    ),
]);

$comparison = (new CoverageComparator())->compare(
    $base,
    $treeCoverage,
    changes: new SourceFileChanges($baseSource, $treeSource, $repository)
);

$changes = new SourceFileChanges($baseSource, $treeSource, $repository, $baseBuild, $treeBuild);

var_dump($comparison->missedLines()->bySource());
var_dump($comparison->gainedLines()->bySource());
var_dump($comparison->missedBranches()->bySource());
var_dump($comparison->gainedBranches()->bySource());
var_dump($changes->source('generated/source.c')->changed());
var_dump($changes->source('generated/source.c')->baseLine(3));

$temporary->remove();
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('changed_source_coverage')
);
?>
--EXPECT--
array(1) {
  ["example/source.c"]=>
  array(2) {
    [0]=>
    int(2)
    [1]=>
    int(5)
  }
}
array(1) {
  ["example/source.c"]=>
  array(1) {
    [0]=>
    int(3)
  }
}
array(1) {
  ["example/source.c"]=>
  array(2) {
    [0]=>
    string(3) "2:0"
    [1]=>
    string(3) "5:0"
  }
}
array(1) {
  ["example/source.c"]=>
  array(1) {
    [0]=>
    string(3) "3:0"
  }
}
bool(true)
int(2)
