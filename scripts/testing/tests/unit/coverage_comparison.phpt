--TEST--
Coverage reports exact gained, missed and uncovered coverage
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\CoverageComparator;
use PHP\Testing\CoverageReporter;
use PHP\Testing\CoverageSnapshot;
use PHP\Testing\PhptChange;
use PHP\Testing\PhptChanges;
use PHP\Testing\PhptRun;
use PHP\Testing\PhptResults;
use PHP\Testing\ProcessMeasurement;
use PHP\Testing\SourceCoverage;

$base = new CoverageSnapshot([
    'example.c' => new SourceCoverage(
        coveredLines: [1, 2],
        executableLines: [1, 2, 3, 4, 6],
        coveredBranches: ['1:0'],
        executableBranches: ['1:0', '2:0', '3:0', '4:0']
    ),
]);

$treeSource = new SourceCoverage(
    coveredLines: [2, 3],
    executableLines: [1, 2, 3, 4, 6],
    coveredBranches: ['2:0'],
    executableBranches: ['1:0', '2:0', '3:0', '4:0']
);

$tree = new CoverageSnapshot([
    'example.c' => $treeSource,
    'new.c' => new SourceCoverage(),
]);

$comparison = (new CoverageComparator())->compare($base, $tree);

var_dump($comparison->missedLines()->bySource());
var_dump($comparison->gainedLines()->bySource());
var_dump($comparison->missedBranches()->bySource());
var_dump($comparison->gainedBranches()->bySource());

$report = __DIR__ . '/coverage_comparison.report';

$baseResults = new PhptResults([
    'deleted.phpt' => 'PASS',
    'old.phpt' => 'PASS',
    'skipped.phpt' => 'PASS',
]);

$treeResults = new PhptResults([
    'created.phpt' => 'SKIP',
    'new.phpt' => 'SKIP',
    'skipped.phpt' => 'SKIP',
], 4);

$testChanges = new PhptChanges([
    new PhptChange(null, 'created.phpt', null, 'SKIP'),
    new PhptChange('deleted.phpt', null, 'PASS', null),
    new PhptChange('old.phpt', 'new.phpt', 'PASS', 'SKIP'),
    new PhptChange('skipped.phpt', 'skipped.phpt', 'PASS', 'SKIP'),
]);

ob_start();
(new CoverageReporter($report))->report(
    $comparison,
    new PhptRun(new ProcessMeasurement(0, 1.0, 1048576), $baseResults),
    new PhptRun(new ProcessMeasurement(0, 2.0, 2097152), $treeResults),
    $testChanges
);
$output = str_replace($report, '<report>', ob_get_clean());

echo $output;
echo "--- Report ---\n", file_get_contents($report);
var_dump($comparison->hasMissedCoverage());

$treeSource->recordLine(5, false);

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
  ["example.c"]=>
  array(1) {
    [0]=>
    int(1)
  }
}
array(1) {
  ["example.c"]=>
  array(1) {
    [0]=>
    int(3)
  }
}
array(1) {
  ["example.c"]=>
  array(1) {
    [0]=>
    string(3) "1:0"
  }
}
array(1) {
  ["example.c"]=>
  array(1) {
    [0]=>
    string(3) "2:0"
  }
}
+--------+---------+---------+------------------+------------------+--------+---------+
|        |   Tests | Sources |            Lines |         Branches |   Time |  Memory |
+--------+---------+---------+------------------+------------------+--------+---------+
| Base   |       3 |       1 |     2/5 (40.00%) |     1/4 (25.00%) |  1.00s |  1.0 MB |
| Tree   |       4 |       2 |     2/5 (40.00%) |     1/4 (25.00%) |  2.00s |  2.0 MB |
| Change | +1 / -1 |      +1 | +1 / -1 (+0.00%) | +1 / -1 (+0.00%) | +1.00s | +1.0 MB |
+--------+---------+---------+------------------+------------------+--------+---------+
Report: <report>
--- Report ---
===== Missed ===================================================================

      Lines (1)
      --------------------------------------------------------------------------
      example.c:
        1

      Branches (1)
      --------------------------------------------------------------------------
      example.c:
        1:0

===== Gained ===================================================================

      Lines (1)
      --------------------------------------------------------------------------
      example.c:
        3

      Branches (1)
      --------------------------------------------------------------------------
      example.c:
        2:0

===== Uncovered ================================================================

      Lines (2)
      --------------------------------------------------------------------------
      example.c:
        4 6

      Branches (2)
      --------------------------------------------------------------------------
      example.c:
        3:0 4:0

===== Tests ====================================================================

      Created (1)
      --------------------------------------------------------------------------
      created.phpt

      Deleted (1)
      --------------------------------------------------------------------------
      deleted.phpt

      Renamed (1)
      --------------------------------------------------------------------------
      old.phpt -> new.phpt

      Skipped (3)
      --------------------------------------------------------------------------
      created.phpt: - -> SKIP
      old.phpt -> new.phpt: PASS -> SKIP
      skipped.phpt: PASS -> SKIP

bool(true)
RuntimeException: Line coverage map changed: example.c
