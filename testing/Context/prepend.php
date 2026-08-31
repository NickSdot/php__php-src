<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Workspace.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php';

// restore shared SETUP variables
TestSuite\Context\Bootstrap::restoreVariables();

// restore original auto_prepend_file
$autoPrependFile = TestSuite\Context\Bootstrap::originalAutoPrependFile();

if ($autoPrependFile !== null) {
    require $autoPrependFile;
}

unset($autoPrependFile);
