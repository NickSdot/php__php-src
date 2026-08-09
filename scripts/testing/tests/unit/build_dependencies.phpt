--TEST--
Build dependencies map changed inputs to compiled sources
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

use PHP\Testing\BuildDependencyReader;
use PHP\Testing\CoverageScope;
use PHP\Testing\CoverageScopeResolver;
use PHP\Testing\PhptSuites;
use PHP\Testing\TestCoverageOptions;
use PHP\Testing\TestTemporaryDirectory;
use PHP\Testing\TestTrees;

$temporary = TestTemporaryDirectory::create(
    TestTemporaryDirectory::stateFile('build_dependencies')
);

$root = $temporary->path();
$source = "$root/source";
$build = "$root/build";

foreach ([
    "$source/vendor/acme/tests",
    "$source/main",
    "$build/vendor/acme",
    "$build/main",
] as $directory) {
    mkdir($directory, recursive: true);
}

foreach ([
    "$source/vendor/acme/first.c",
    "$source/vendor/acme/shared header.h",
    "$source/main/core.c",
    "$source/main/core.h",
] as $file) {
    file_put_contents($file, '');
}

$escaped = fn(string $path): string => str_replace(' ', '\\ ', $path);

file_put_contents("$build/vendor/acme/first.dep", sprintf(
    "vendor/acme/first.lo: %s %s %s\n",
    $escaped("$source/vendor/acme/first.c"),
    $escaped("$source/vendor/acme/shared header.h"),
    $escaped("$source/main/core.h")
));

file_put_contents("$build/main/core.dep", sprintf(
    "main/core.lo: %s %s\n",
    $escaped("$source/main/core.c"),
    $escaped("$source/main/core.h")
));

$dependencies = (new BuildDependencyReader())->read($build, $source);

var_dump($dependencies->affectedSources(['vendor/acme/shared header.h']));
var_dump($dependencies->affectedSources(['main/core.h']));
var_dump($dependencies->affectedSources(['docs/readme.md']));
var_dump($dependencies->coverageFiles(CoverageScope::paths(['vendor/acme'])));
var_dump($dependencies->coverageFiles(CoverageScope::global()));
var_dump(file_exists("$build/.deps"));

$trees = new TestTrees($source, $source, new PhptSuites(
    ["$source/vendor/acme/tests/example.phpt"],
    ["$source/vendor/acme/tests/example.phpt"]
));
$options = new TestCoverageOptions('master', [], ['vendor/acme/tests'], false, false);
$resolver = new CoverageScopeResolver();

var_dump($resolver->resolve($options, $trees, ['vendor/acme/shared header.h'], $dependencies)->description());
var_dump($resolver->resolve($options, $trees, ['main/core.h'], $dependencies)->description());
?>
--CLEAN--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';
require dirname(__DIR__) . '/TestTemporaryDirectory.inc.php';

PHP\Testing\TestTemporaryDirectory::removeFromStateFile(
    PHP\Testing\TestTemporaryDirectory::stateFile('build_dependencies')
);
?>
--EXPECT--
array(1) {
  [0]=>
  string(19) "vendor/acme/first.c"
}
array(2) {
  [0]=>
  string(11) "main/core.c"
  [1]=>
  string(19) "vendor/acme/first.c"
}
array(0) {
}
array(1) {
  [0]=>
  string(22) "vendor/acme/first.gcda"
}
NULL
bool(true)
string(11) "vendor/acme"
string(6) "global"
