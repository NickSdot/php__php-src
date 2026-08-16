--TEST--
Handles test-only, unrelated, C-only, mixed and negative changes
--CONFLICTS--
all
--ENV--
TEST_TIMEOUT=1200
--INI--
max_execution_time=0
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';
require dirname(__DIR__) . '/IntegrationTestWorkspace.inc.php';
require dirname(__DIR__) . '/CoverageIntegrationTest.inc.php';

use PHP\Testing\CoverageIntegrationTest;
use PHP\Testing\TestTemporaryDirectory;

$test = CoverageIntegrationTest::create(
    dirname(__DIR__, 4),
    TestTemporaryDirectory::stateFile('validate_test_coverage')
);

$test->compareUnrelated('Test-only', $test->base, $test->covered, 'Gained');
$test->compareUnrelated('Documentation-only', $test->covered, $test->unrelated);
$test->compare('C-only', $test->unrelated, $test->changed);
$test->compare('Mixed', $test->base, $test->changed);
$test->compare('Negative', $test->changed, $test->negative, missed: true);
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';
require dirname(__DIR__) . '/IntegrationTestWorkspace.inc.php';

PHP\Testing\IntegrationTestWorkspace::remove(
    dirname(__DIR__, 4),
    PHP\Testing\TestTemporaryDirectory::stateFile('validate_test_coverage')
);
?>
--EXPECT--
Test-only: OK
Documentation-only: OK
C-only: OK
Mixed: OK
Negative: OK
