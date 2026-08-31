--TEST--
Test that return types in zend_func_info.c match return types in stubs
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.optimization_level=-1
--EXTENSIONS--
opcache
--FILE--
<?php

$contents = "<?php\n";

$contents .= "function test() {\n";
foreach (get_defined_functions()["internal"] as $function) {
    if (in_array($function, ["extract", "compact", "get_defined_vars"])) {
        continue;
    }
    $contents .= "    \$result = \\{$function}();\n";
}
$contents .= "}\n";

$file = Workspace::file();
file_put_contents($file, $contents);

require_once($file);

?>
--EXPECT--
