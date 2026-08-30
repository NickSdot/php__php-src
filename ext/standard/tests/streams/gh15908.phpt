--TEST--
GH-15908 (leak / assertion failure in streams.c)
--CREDITS--
YuanchengJiang
LuMingYinDetect
--FILE--
<?php
class TestStream {
    public $context;
    private $s = 0;
    function stream_open($path, $mode, $options, &$opened_path) {
        return true;
    }
    function stream_read($count) {
        echo "Read done\n";
        if ($this->s++ == 0)
            return "a\nbb\ncc";
        return "";
    }
    function stream_eof() {
        return $this->s >= 2;
    }
}
$tmp = TestPath::file()->create();
stream_wrapper_register("test", "TestStream");
$f = fopen("test://", "r");
try {
    file_put_contents($tmp, $f, FILE_USE_INCLUDE_PATH, $f);
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
?>
--EXPECT--
file_put_contents(): supplied resource is not a valid Stream-Context resource
