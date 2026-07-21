--TEST--
pcntl_exec(): Test cleanup after non-stringable array value has been encountered for $args and $env_vars.
--EXTENSIONS--
pcntl
--FILE--
<?php
try {
    pcntl_exec('cmd', ['-n', new stdClass()]);
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}

try {
    pcntl_exec(
        'cmd',
        ['-n'],
        ['var1' => 'value1', 'var2' => new stdClass()],
    );
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), PHP_EOL;
}
?>
--EXPECT--
Error: Object of class stdClass could not be converted to string
Error: Object of class stdClass could not be converted to string
