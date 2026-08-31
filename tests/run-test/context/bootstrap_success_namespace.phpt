--TEST--
Test context bootstrap - namespaces - Workspace is available
--FILE--
<?php

namespace PhpTestContext;

use Workspace;

var_dump(Workspace::file('namespaced.txt')->name);

?>
--EXPECT--
string(14) "namespaced.txt"
