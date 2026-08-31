--TEST--
Phar::buildFromDirectory() with non-matching regex
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
phar.readonly=0
--FILE--
<?php

$directory = Workspace::directory()->create();
$files = [];
foreach(range(1, 4) as $i) {
    $files[$i] = $directory->file("file$i.txt");
    file_put_contents($files[$i], "some content for file $i");
}

try {
    $archive = Workspace::file('buildfromdirectory6.phar');
    $phar = new Phar($archive->path);
    var_dump($phar->buildFromDirectory($directory->path, '/\.php$/'));
} catch (Exception $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

var_dump(file_exists($archive));

?>
--EXPECT--
array(0) {
}
bool(false)
