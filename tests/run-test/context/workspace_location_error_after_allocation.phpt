--TEST--
The workspace location cannot change after the first path allocation
--FILE--
<?php

TestPath::file();

try {
    TestContext::useRelativeWorkspace();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
LogicException: Workspace directory is already set
