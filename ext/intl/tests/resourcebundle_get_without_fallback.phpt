--TEST--
ResourceBundle::get() rejects fallback results when fallback is disabled with correct error message
--EXTENSIONS--
intl
--FILE--
<?php
require_once "resourcebundle.inc";

Workspace::useRelative();
$fixture = Workspace::directory()->create();
$rootBundle = $fixture->file('root.res');
copy(BUNDLE . '/root.res', $rootBundle);

$resIndex = file_get_contents(BUNDLE . '/res_index.res');
$resIndex = str_replace("\0es\0root", "\0en\0root", $resIndex, $replacementCount);
if ($replacementCount !== 1) {
    die("Failed to prepare resource bundle index\n");
}
$resourceIndex = $fixture->file('res_index.res');
file_put_contents($resourceIndex, $resIndex);

// Keep the binary layout intact while making this key fall back to root.res.
$enBundle = file_get_contents(BUNDLE . '/es.res');
$enBundle = str_replace('teststring', 'teststrinh', $enBundle, $replacementCount);
if ($replacementCount !== 1) {
    die("Failed to prepare resource bundle fixture\n");
}
$enBundleFile = $fixture->file('en.res');
file_put_contents($enBundleFile, $enBundle);

$bundle = new ResourceBundle('en', $fixture);
echo debug($bundle->get('teststring', false));

$bundle = resourcebundle_create('en', $fixture);
echo debug(resourcebundle_get($bundle, 'teststring', false));
?>
--EXPECTF--
NULL
 %i: ResourceBundle::get(): Cannot load element 'teststring' without fallback to %s: U_USING_%s_WARNING
NULL
 %i: resourcebundle_get(): Cannot load element 'teststring' without fallback to %s: U_USING_%s_WARNING
