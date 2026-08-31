--TEST--
Bug #64342 ZipArchive::addFile() has to check file existence (variation 1)
--EXTENSIONS--
zip
--FILE--
<?php

$file = Workspace::file('archive.zip');
$zip = new ZipArchive;
$res = $zip->open($file, ZipArchive::CREATE);
if ($res === TRUE) {
    $f = md5(uniqid()) . '.txt';
    echo "$f\n";
    $res = $zip->addFile($f);
    if (true == $res) {
        echo "add ok\n";
    } else {
        echo "add failed\n";
    }
    $res = $zip->close();
    if (true == $res) {
        echo "close ok\n";
    } else {
        echo "close failed\n";
    }
} else {
    echo "open failed\n";
}


?>
DONE
--EXPECTF--
%s.txt

Warning: ZipArchive::addFile(): No such file or directory in %s on line %d
add failed
close ok
DONE
