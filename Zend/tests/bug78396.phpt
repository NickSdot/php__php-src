--TEST--
Bug #78396: Second file_put_contents in Shutdown hangs script
--FILE--
<?php

$file = Workspace::file();
register_shutdown_function(function () use ($file) {
    file_put_contents($file, '1', FILE_APPEND | LOCK_EX);
    file_put_contents($file, '2', FILE_APPEND | LOCK_EX);
    echo "Done\n";
});

?>
--EXPECT--
Done
