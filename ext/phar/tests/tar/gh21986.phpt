--TEST--
GH-21986 (PharData::getContent() crash on circular symlink chain in tar)
--EXTENSIONS--
phar
--FILE--
<?php
function tar_symlink($name, $target) {
    $h  = str_pad($name, 100, "\0");
    $h .= "0000777\0";
    $h .= "0000000\0";
    $h .= "0000000\0";
    $h .= "00000000000\0";
    $h .= "00000000000\0";
    $h .= str_repeat(' ', 8);
    $h .= '2';
    $h .= str_pad($target, 100, "\0");
    $h .= "ustar\0" . "00";
    $h  = str_pad($h, 512, "\0");

    $sum = 0;
    for ($i = 0; $i < 512; $i++) {
        $sum += ord($h[$i]);
    }
    return substr_replace($h, sprintf("%06o\0 ", $sum), 148, 8);
}

$archive = Workspace::file('archive.tar');
$tar = $archive->path;
file_put_contents(
    $tar,
    tar_symlink('a.txt', 'b.txt') .
    tar_symlink('b.txt', 'a.txt') .
    str_repeat("\0", 1024)
);

$phar = new PharData($tar);
var_dump($phar['a.txt']->getContent());
?>
--EXPECT--
string(0) ""
