<?php

include 'config.inc';
require_once dirname(__DIR__, 3) . '/tests/probe_cache.inc';

$reason = ProbeCache::getFailure('odbc', [$dsn, $user, $pass], static function () use ($dsn, $user, $pass): ?string {
    $conn = @odbc_connect($dsn, $user, $pass);
    if (!$conn) {
        return 'could not connect';
    }

    odbc_close($conn);
    return null;
});

if (is_string($reason)) {
    die("skip $reason");
}
