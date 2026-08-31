--TEST--
Bug #78862 (link() silently truncates after a null byte on Windows)
--FILE--
<?php
$target = Workspace::file()->path;
$link = Workspace::file()->path;
file_put_contents($target, 'foo');
var_dump(link($target . "\0more", $link . "\0more"));
var_dump(file_exists($link));
?>
--EXPECTF--
Fatal error: Uncaught ValueError: link(): Argument #1 ($target) must not contain any null bytes in %s:%d
Stack trace:
#0 %s(%d): link('%s', '%s')
#1 {main}
  thrown in %s on line %d
