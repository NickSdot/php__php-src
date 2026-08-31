--TEST--
Test Workspace::useRelative() - selection after accessing the workspace path is rejected
--FILE--
<?php

Workspace::path();

try {
    Workspace::useRelative();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
LogicException: Workspace directory is already set
