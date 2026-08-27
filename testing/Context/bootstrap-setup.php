<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestContext.php';

$autoPrependFile = getenv('PHP_TEST_CONTEXT_AUTO_PREPEND_FILE');

if ($autoPrependFile !== false && $autoPrependFile !== '') {
    require $autoPrependFile;
}
