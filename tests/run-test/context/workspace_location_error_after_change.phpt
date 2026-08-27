--TEST--
The workspace location can only be changed once
--SETUP--
<?php

TestContext::useRelativeWorkspace();

?>
--FILE--
<?php

try {
    TestContext::useSystemWorkspace();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
LogicException: Workspace directory is already set
