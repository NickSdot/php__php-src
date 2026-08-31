--TEST--
stream_resolve_include_path(string path)
--FILE--
<?php
$include_path = Workspace::directory('test_path')->create();
$include_path_nested = $include_path->directory('nested')->create();

$include_path_file = $include_path->file('file');
$include_path_nested_file = $include_path_nested->file('file');

file_put_contents($include_path_file, 'include_path');
file_put_contents($include_path_nested_file, 'include_path');

set_include_path($include_path . PATH_SEPARATOR . $include_path_nested);
var_dump(stream_resolve_include_path('file-does-not-exist'));

set_include_path($include_path . PATH_SEPARATOR . $include_path_nested);
var_dump(stream_resolve_include_path('file'));
set_include_path($include_path_nested . PATH_SEPARATOR . $include_path);
var_dump(stream_resolve_include_path('file'));

?>
--EXPECTF--
bool(false)
string(%d) "%stest_path%sfile"
string(%d) "%stest_path%snested%sfile"
