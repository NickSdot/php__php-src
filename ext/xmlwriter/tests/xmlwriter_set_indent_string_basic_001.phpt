--TEST--
xmlwriter_set_indent_string passing xmlwriter resource
--EXTENSIONS--
xmlwriter
--FILE--
<?php
$temp_filename = TestPath::file();
    $fp = fopen($temp_filename, "w");
    fwrite ($fp, "Hi");
    fclose($fp);
$resource = xmlwriter_open_uri($temp_filename);
var_dump(xmlwriter_set_indent_string($resource, '  '));
?>
--CREDITS--
Koen Kuipers koenk82@gmail.com
Theo van der Zee
#Test Fest Utrecht 09-05-2009
--EXPECT--
bool(true)
