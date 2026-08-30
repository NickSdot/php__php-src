--TEST--
Bug #37864 (file_get_contents() leaks on empty file)
--FILE--
<?php
    $tmpfname = TestPath::file()->create();
    var_dump(file_get_contents($tmpfname));
    echo "done.\n";
?>
--EXPECT--
string(0) ""
done.
