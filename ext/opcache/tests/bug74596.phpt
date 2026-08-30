--TEST--
Bug #74596 (SIGSEGV with opcache.revalidate_path enabled)
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.validate_timestamps=0
opcache.file_cache_only=0
opcache.revalidate_path=1
--EXTENSIONS--
opcache
--FILE--
<?php

$file1 = TestPath::file();
$file2 = TestPath::file();
file_put_contents($file1, <<<CODE
<?php
class A {
    public function __construct() {
        \$a = true;
        if (\$a) {
            echo 1 + 2;
        } else {
            echo 2 + 3;
        }
    }
}
?>
CODE
);

file_put_contents($file2, "ok\n");

class ufilter extends php_user_filter
{
    public static $file1;

    function filter($in, $out, &$consumed, $closing): int
    {
        include_once self::$file1;
        while ($bucket = stream_bucket_make_writeable($in)) {
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}

ufilter::$file1 = $file1;
stream_filter_register("ufilter", "ufilter");

include "php://filter/read=ufilter/resource=" . $file2;
?>
--EXPECT--
ok
