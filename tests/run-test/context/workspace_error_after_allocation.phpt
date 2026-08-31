--TEST--
Test Workspace::useRelative() - selection after allocation is rejected
--FILE--
<?php

Workspace::file();

try {
    Workspace::useRelative();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
LogicException: Workspace directory is already set
