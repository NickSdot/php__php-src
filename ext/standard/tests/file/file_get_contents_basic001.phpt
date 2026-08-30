--TEST--
file_get_contents() test using basic syntax
--CREDITS--
"Blanche V.N." <valerie_nare@yahoo.fr>
--FILE--
<?php
    $file_content = "Bienvenue au CodeFest a Montreal";
    $temp_filename = TestPath::file();
    $handle = fopen($temp_filename,"w");
    fwrite($handle,$file_content);
    fclose($handle);
    $var = file_get_contents($temp_filename);
    echo $var;
?>
--EXPECT--
Bienvenue au CodeFest a Montreal
