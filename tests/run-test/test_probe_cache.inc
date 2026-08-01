<?php

function run_test_cache_probe_failure(string $namespace, array $configuration, callable $probe): ?string
{
    $directory = getenv('TEST_PHP_SHARED_CACHE_DIR');
    if (!is_string($directory) || !is_dir($directory)) {
        return $probe();
    }

    $cacheFile = $directory
        . DIRECTORY_SEPARATOR
        . 'probe-'
        . hash('sha256', serialize([$namespace, $configuration]));
    $cache = @fopen($cacheFile, 'c+');
    if ($cache === false || !flock($cache, LOCK_EX)) {
        if (is_resource($cache)) {
            fclose($cache);
        }
        return $probe();
    }

    try {
        $cached = stream_get_contents($cache);
        if (is_string($cached) && $cached !== '' && $cached[0] === 'f') {
            return substr($cached, 1);
        }

        $failure = $probe();
        if (is_string($failure)) {
            rewind($cache);
            ftruncate($cache, 0);
            fwrite($cache, "f$failure");
            fflush($cache);
        }

        return $failure;
    } finally {
        flock($cache, LOCK_UN);
        fclose($cache);
    }
}
