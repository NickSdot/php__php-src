--TEST--
Bug #42364 (Crash when using getRealPath with DirectoryIterator)
--FILE--
<?php
$dir = Workspace::directory()->create();
$dir->file()->create();

$count = 0;
$it = new DirectoryIterator($dir);
foreach ($it as $e) {
    $count++;
    $type = gettype($e->getRealPath());
    if ($type != "string" && $type != "unicode") {
        echo $e->getFilename(), " is a ", gettype($e->getRealPath()), "\n";
    }
}

if ($count > 0) {
    echo "Found $count entries!\n";
}
?>
--EXPECTF--
Found %i entries!
