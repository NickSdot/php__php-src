--TEST--
Test Workspace allocation - invalid names - empty, null bytes and directory components
--FILE--
<?php

$names = [
    '',
    "invalid\0name",
    '.',
    '..',
    'directory/file',
    'directory\\file',
];

foreach (['file', 'directory'] as $method) {
    echo "=== $method ===\n";
    foreach ($names as $name) {
        try {
            Workspace::$method($name);
        } catch (Throwable $e) {
            echo $e::class, ': ', $e->getMessage(), "\n";
        }
    }
}

?>
--EXPECT--
=== file ===
ValueError: Name cannot be empty
ValueError: Name cannot contain null bytes
ValueError: Name cannot be . or ..
ValueError: Name cannot be . or ..
ValueError: Name cannot contain directory separators
ValueError: Name cannot contain directory separators
=== directory ===
ValueError: Name cannot be empty
ValueError: Name cannot contain null bytes
ValueError: Name cannot be . or ..
ValueError: Name cannot be . or ..
ValueError: Name cannot contain directory separators
ValueError: Name cannot contain directory separators
