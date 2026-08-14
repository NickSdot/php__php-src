--TEST--
Coverage reports exact gained and missed coverage
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\CoverageComparator;
use PHP\Testing\CoverageReporter;
use PHP\Testing\CoverageSnapshot;
use PHP\Testing\PhptRun;
use PHP\Testing\SourceCoverage;

$base = new CoverageSnapshot([
    'example.c' => new SourceCoverage(
        coveredLines: [1, 2],
        executableLines: [1, 2, 3],
        coveredBranches: ['1:0'],
        executableBranches: ['1:0', '2:0']
    ),
]);

$treeSource = new SourceCoverage(
    coveredLines: [2, 3],
    executableLines: [1, 2, 3],
    coveredBranches: ['2:0'],
    executableBranches: ['1:0', '2:0']
);

$tree = new CoverageSnapshot([
    'example.c' => $treeSource,
    'new.c' => new SourceCoverage(),
]);

$comparison = (new CoverageComparator())->compare($base, $tree);

var_dump($comparison->missedLines());
var_dump($comparison->gainedLines());
var_dump($comparison->missedBranches());
var_dump($comparison->gainedBranches());

$report = __DIR__ . '/coverage_comparison.report';

ob_start();
(new CoverageReporter($report))->report(
    $comparison,
    new PhptRun(0, 1.0, 1048576, 3),
    new PhptRun(0, 2.0, 2097152, 4)
);
$output = str_replace($report, '<report>', ob_get_clean());

echo $output;
echo "--- Report ---\n", file_get_contents($report);
var_dump($comparison->hasMissedCoverage());

$treeSource->recordLine(4, false);

try {
    (new CoverageComparator())->compare($base, $tree);
} catch (RuntimeException $exception) {
    echo $exception::class, ': ', $exception->getMessage(), "\n";
}

?>
--CLEAN--
<?php
$report = __DIR__ . '/coverage_comparison.report';

if (is_file($report) === true) {
    unlink($report);
}
?>
--EXPECT--
array(1) {
  [0]=>
  string(11) "example.c:1"
}
array(1) {
  [0]=>
  string(11) "example.c:3"
}
array(1) {
  [0]=>
  string(13) "example.c:1:0"
}
array(1) {
  [0]=>
  string(13) "example.c:2:0"
}
+--------+-------+---------+------------------+------------------+--------+---------+
|        | Tests | Sources |            Lines |         Branches |   Time |  Memory |
+--------+-------+---------+------------------+------------------+--------+---------+
| Base   |     3 |       1 |     2/3 (66.67%) |     1/2 (50.00%) |  1.00s |  1.0 MB |
| Tree   |     4 |       2 |     2/3 (66.67%) |     1/2 (50.00%) |  2.00s |  2.0 MB |
| Change |    +1 |      +1 | +1 / -1 (+0.00%) | +1 / -1 (+0.00%) | +1.00s | +1.0 MB |
+--------+-------+---------+------------------+------------------+--------+---------+
Report: <report>
--- Report ---
Missed
======

Lines (1)
---------
example.c:1

Branches (1)
------------
example.c:1:0

Gained
======

Lines (1)
---------
example.c:3

Branches (1)
------------
example.c:2:0

bool(true)
RuntimeException: Line coverage map changed: example.c
