--TEST--
Bug #44461 (parse_ini_file crashes)
--FILE--
<?php
$file = Workspace::file();
file_put_contents($file, <<<EOF
[attachments]
zip = "application/zip" ; MIME-type for ZIP files
EOF
);
parse_ini_file($file, true);
?>
===DONE===
--EXPECT--
===DONE===
