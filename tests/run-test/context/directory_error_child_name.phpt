--TEST--
Test Directory child allocation - directory separators are rejected by both child methods
--FILE--
<?php

$directory = Workspace::directory();

foreach (['file', 'directory'] as $method) {
    try {
        $directory->$method('nested/file');
    } catch (Throwable $e) {
        echo $e::class, ': ', $e->getMessage(), "\n";
    }
}

?>
--EXPECT--
ValueError: Name cannot contain directory separators
ValueError: Name cannot contain directory separators
