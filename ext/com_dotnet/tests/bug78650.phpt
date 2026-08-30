--TEST--
Bug #78650 (new COM Crash)
--EXTENSIONS--
com_dotnet
--FILE--
<?php
$parent = TestPath::directory();
$fname = $parent->directory();
mkdir($fname, 0777, true);

$fso = new COM("Scripting.FileSystemObject");
$folder = $fso->GetFolder($fname);
$folder->ParentFolder->Name = 'baz';

print('OK');
?>
--EXPECT--
OK
