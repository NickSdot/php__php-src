--TEST--
Selecting the same non-default workspace location twice is rejected
--SETUP--
<?php

TestContext::useRelativeWorkspace();

?>
--FILE--
<?php

try {
    TestContext::useRelativeWorkspace();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
LogicException: Workspace directory is already set
