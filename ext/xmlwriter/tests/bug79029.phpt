--TEST--
#79029 (Use After Free's in XMLReader / XMLWriter)
--EXTENSIONS--
xmlwriter
xmlreader
--FILE--
<?php
$x = array( new XMLWriter() );
$file1 = Workspace::file();
$x[0]->openUri($file1);
$x[0]->startComment();

$y = new XMLWriter();
$file2 = Workspace::file();
$y->openUri($file2);
fclose(@end(get_resources()));

$file3 = Workspace::file();
file_put_contents($file3, "a");
$z = new XMLReader();
$z->open($file3);
fclose(@end(get_resources()));
?>
okey
--EXPECTF--
Warning: fclose(): cannot close the provided stream, as it must not be manually closed in %sbug79029.%d.php on line %d

Warning: fclose(): cannot close the provided stream, as it must not be manually closed in %sbug79029.%d.php on line %d
okey
