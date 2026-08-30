--TEST--
Test file_put_contents() and file_get_contents() functions with 5GB string
--SKIPIF--
<?php
if (!getenv('RUN_RESOURCE_HEAVY_TESTS')) die('skip resource-heavy test');
if (PHP_INT_SIZE < 5) {
    // 4=4gb, 5=549gb, 8=9exabytes
    die("skip PHP_INT_SIZE<5 will not fit test string in RAM");
}
if (getenv('SKIP_SLOW_TESTS')) {
    die('skip slow test');
}
function get_system_memory(): int|float|false
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows-based memory check
        @exec('wmic OS get FreePhysicalMemory', $output);
        if (isset($output[1])) {
            return ((int)trim($output[1])) * 1024;
        }
    } else {
        // Unix/Linux-based memory check
        $memInfo = @file_get_contents("/proc/meminfo");
        if ($memInfo) {
            preg_match('/MemFree:\s+(\d+) kB/', $memInfo, $matches);
            return $matches[1] * 1024; // Convert to bytes
        }
    }
    return false;
}
if (get_system_memory() < 10 * 1024 * 1024 * 1024) {
    die('skip Reason: Insufficient RAM (less than 10GB)');
}
$probe = tempnam(sys_get_temp_dir(), 'php');
if ($probe === false) {
    die('skip Reason: Unable to create temporary file');
}
$directory = dirname($probe);
unlink($probe);
if (disk_free_space($directory) < 10 * 1024 * 1024 * 1024) {
    die('skip Reason: Insufficient disk space (less than 10GB)');
}
?>
--INI--
memory_limit=6G
--FILE--
<?php
TestContext::useSystemWorkspace();
$tmpfile = TestPath::file();
$large_string_len = 5 * 1024 * 1024 * 1024;

$large_string = str_repeat('a', $large_string_len);
$result = file_put_contents($tmpfile, $large_string);
if ($result !== $large_string_len) {
    echo "Could only write $result bytes of $large_string_len bytes.";
    var_dump(error_get_last());
} else {
    echo "File written successfully." . PHP_EOL;
}
unset($large_string);

$result_large_string = file_get_contents($tmpfile);
if (strlen($result_large_string) !== $large_string_len) {
    echo "Could only read " . strlen($result_large_string) . " bytes of $large_string_len bytes.";
    var_dump(error_get_last());
} else {
    echo "File read successfully." . PHP_EOL;
}

?>
--EXPECT--
File written successfully.
File read successfully.
