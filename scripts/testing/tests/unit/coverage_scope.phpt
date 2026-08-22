--TEST--
Coverage scope resolves test and changed source components
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\CoverageScope;
use PHP\Testing\CoverageScopeResolver;
use PHP\Testing\CoverageSnapshot;
use PHP\Testing\BuildDependencies;
use PHP\Testing\PhptSuites;
use PHP\Testing\SourceCoverage;
use PHP\Testing\TestCoverageOptions;
use PHP\Testing\TestTrees;

function options(array $sources = [], array $tests = ['ext/uri/tests'], bool $global = false): TestCoverageOptions
{
    return new TestCoverageOptions('master', null, $sources, $tests, $global, 10, false);
}

function trees(array $base, array $tree): TestTrees
{
    $root = dirname(__DIR__, 4);

    return new TestTrees($root, $root, new PhptSuites(
        array_map(fn(string $path): string => "$root/$path", $base),
        array_map(fn(string $path): string => "$root/$path", $tree)
    ));
}

$coverage = new CoverageSnapshot([
    'Zend/zend_compile.c' => new SourceCoverage(),
    'ext/json/json.c' => new SourceCoverage(),
    'ext/standard/string.c' => new SourceCoverage(),
    'ext/uri/php_uri.c' => new SourceCoverage(),
    'ext/uri/uri_parser_whatwg.c' => new SourceCoverage(),
    'ext/uri/uriparser/src/UriParse.c' => new SourceCoverage(),
    'vendor/acme/extension.c' => new SourceCoverage(),
]);

$resolver = new CoverageScopeResolver();

$selected = trees(
    ['ext/uri/tests/foo/bar/first.phpt'],
    ['ext/uri/tests/foo/bar/first.phpt', 'ext/uri/tests/bar/baz.phpt']
);

$scope = $resolver->resolve(
    options(),
    $selected,
    ['Zend/zend_compile.c', 'docs/testing.rst', 'ext/standard/tests/other.phpt', 'ext/uri/tests/new.phpt', 'scripts/testing/src/Command.php'],
    new BuildDependencies(
        ['Zend/zend_compile.c' => ['Zend/zend_compile.c' => true]],
        []
    )
);

var_dump($scope->description());
var_dump($scope->sources($coverage, new CoverageSnapshot()));

$vendoredChange = $resolver->resolve(
    options(),
    $selected,
    ['ext/uri/uriparser/src/UriParse.c'],
    new BuildDependencies(
        ['ext/uri/uriparser/src/UriParse.c' => ['ext/uri/uriparser/src/UriParse.c' => true]],
        []
    )
);

var_dump($vendoredChange->sources($coverage, new CoverageSnapshot()));

$multiple = $resolver->resolve(
    options(tests: ['ext/uri/tests', 'ext/json/tests']),
    trees(
        ['ext/uri/tests/uri.phpt', 'ext/json/tests/json.phpt'],
        ['ext/uri/tests/uri.phpt', 'ext/json/tests/json.phpt']
    ),
    []
);

var_dump($multiple->description());
var_dump($multiple->sources($coverage, new CoverageSnapshot()));

$external = $resolver->resolve(
    options(tests: ['vendor/acme/tests']),
    trees(['vendor/acme/tests/example.phpt'], ['vendor/acme/tests/example.phpt']),
    []
);
var_dump($external->description());
var_dump($external->sources($coverage, new CoverageSnapshot()));

$explicit = $resolver->resolve(options(['ext/uri/'], []), $selected, ['Zend/zend_compile.c']);
var_dump($explicit->description());
var_dump($explicit->sources($coverage, new CoverageSnapshot()));

var_dump($explicit->includes('ext/uri/uriparser/src/UriParse.c'));

$explicitVendored = $resolver->resolve(options(['ext/uri/uriparser'], []), $selected, []);
var_dump($explicitVendored->sources($coverage, new CoverageSnapshot()));
var_dump($explicitVendored->includes('ext/uri/uriparser/src/UriParse.c'));

$explicitVendoredChange = $resolver->resolve(
    options(['ext/uri'], []),
    $selected,
    ['ext/uri/uriparser/src/UriParse.c'],
    new BuildDependencies(
        ['ext/uri/uriparser/src/UriParse.c' => ['ext/uri/uriparser/src/UriParse.c' => true]],
        []
    )
);
var_dump($explicitVendoredChange->sources($coverage, new CoverageSnapshot()));
var_dump($explicitVendoredChange->includes('ext/uri/uriparser/src/UriParse.c'));

$complete = $resolver->resolve(options(tests: []), $selected, ['Zend/zend_compile.c']);
var_dump($complete->description());

$global = $resolver->resolve(options(global: true), $selected, []);
var_dump($global->description());
var_dump(count($global->sources($coverage, new CoverageSnapshot())));

$ambiguous = $resolver->resolve(
    options(tests: ['tests']),
    trees(['tests/basic/first.phpt'], ['tests/basic/first.phpt']),
    []
);
var_dump($ambiguous->description());

try {
    CoverageScope::paths(['ext/missing'])->sources($coverage, new CoverageSnapshot());
} catch (Throwable $e) {
	echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECT--
string(13) "Zend, ext/uri"
array(3) {
  [0]=>
  string(19) "Zend/zend_compile.c"
  [1]=>
  string(17) "ext/uri/php_uri.c"
  [2]=>
  string(27) "ext/uri/uri_parser_whatwg.c"
}
array(3) {
  [0]=>
  string(17) "ext/uri/php_uri.c"
  [1]=>
  string(27) "ext/uri/uri_parser_whatwg.c"
  [2]=>
  string(32) "ext/uri/uriparser/src/UriParse.c"
}
string(17) "ext/json, ext/uri"
array(3) {
  [0]=>
  string(15) "ext/json/json.c"
  [1]=>
  string(17) "ext/uri/php_uri.c"
  [2]=>
  string(27) "ext/uri/uri_parser_whatwg.c"
}
string(11) "vendor/acme"
array(1) {
  [0]=>
  string(23) "vendor/acme/extension.c"
}
string(7) "ext/uri"
array(2) {
  [0]=>
  string(17) "ext/uri/php_uri.c"
  [1]=>
  string(27) "ext/uri/uri_parser_whatwg.c"
}
bool(false)
array(1) {
  [0]=>
  string(32) "ext/uri/uriparser/src/UriParse.c"
}
bool(true)
array(3) {
  [0]=>
  string(17) "ext/uri/php_uri.c"
  [1]=>
  string(27) "ext/uri/uri_parser_whatwg.c"
  [2]=>
  string(32) "ext/uri/uriparser/src/UriParse.c"
}
bool(true)
string(6) "global"
string(6) "global"
int(7)
string(6) "global"
RuntimeException: Coverage scope was not exercised: ext/missing
