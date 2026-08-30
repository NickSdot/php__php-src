--TEST--
Phar::buildFromDirectory() with non-matching regex
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php

$directory = TestPath::directory();
mkdir($directory->path, 0777);
$files = [];
foreach(range(1, 4) as $i) {
    $files[$i] = $directory->file("file$i.txt");
    file_put_contents($files[$i], "some content for file $i");
}

try {
    $archive = TestPath::file('buildfromdirectory6.phar');
    $phar = new Phar($archive->path);
    var_dump($phar->buildFromDirectory($directory->path, '/\.php$/'));
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

var_dump(file_exists(__DIR__ . '/buildfromdirectory6.phar'));

?>
<?php
foreach(range(1, 4) as $i) {
}
--EXPECT--
array(0) {
}
bool(false)
