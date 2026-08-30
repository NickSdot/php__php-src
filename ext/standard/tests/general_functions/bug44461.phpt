--TEST--
Bug #44461 (parse_ini_file crashes)
--FILE--
<?php
$file = TestPath::file('bug44461.ini');
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
