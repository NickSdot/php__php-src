--TEST--
Test Workspace::useRelative() - repeated selection across sections is rejected
--SETUP--
<?php

Workspace::useRelative();

?>
--FILE--
<?php

try {
    Workspace::useRelative();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
LogicException: Workspace directory is already set
