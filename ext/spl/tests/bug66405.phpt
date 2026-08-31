--TEST--
SPL: RecursiveDirectoryIterator with CURRENT_AS_PATHNAME flag
--CREDITS--
Paul Garvin pgarvin76@gmail.com
--FILE--
<?php
$td = Workspace::directory('bug66405')->create();
$td->file('file1.txt')->create();
$td->file('file2.md')->create();
$td->directory('testsubdir')->create()->file('file3.csv')->create();

class Bug66405 extends RecursiveDirectoryIterator
{
    public function current(): string|SplFileInfo|FilesystemIterator
    {
        $current = parent::current();
        echo gettype($current) . " $current\n";
        return $current;
    }

    public function getChildren(): RecursiveDirectoryIterator
    {
        $children = parent::getChildren();
        if (is_object($children)) {
            echo get_class($children) . " $children\n";
        } else {
            echo gettype($children) . " $children\n";
        }
        return $children;
    }
}

$rdi = new Bug66405($td, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS);
$rii = new RecursiveIteratorIterator($rdi);

ob_start();
foreach ($rii as $file) {
    //noop
}
$results = explode("\n", ob_get_clean());
sort($results);
echo implode("\n", $results);
?>
--EXPECTF--
Bug66405 file3.csv
string %sbug66405%efile1.txt
string %sbug66405%efile2.md
string %sbug66405%etestsubdir%efile3.csv
