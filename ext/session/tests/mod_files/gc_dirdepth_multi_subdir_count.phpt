--TEST--
session GC accumulates correct total count across multiple subdirs, including empty ones (dirdepth=1)
--EXTENSIONS--
session
--SKIPIF--
<?php include(__DIR__ . '/../skipif.inc'); ?>
--INI--
session.gc_probability=0
session.gc_maxlifetime=10
session.use_strict_mode=0
--FILE--
<?php
$base = TestPath::directory()->create();
$a = $base->directory('a');
$b = $base->directory('b');
$c = $base->directory('c');
$d = $base->directory('d');   // empty subdir
mkdir($a);
mkdir($b);
mkdir($c);
mkdir($d);

session_save_path("1;$base");

$files = [
    $a->file('sess_aexpired0000000000000000'),
    $b->file('sess_bexpired0000000000000000'),
    $c->file('sess_cexpired0000000000000000'),
];
foreach ($files as $f) {
    file_put_contents($f, 'user|s:5:"alice";');
    touch($f, time() - 100);
}

session_id('a0000000000000000000000000');
session_start();
$result = session_gc();
session_destroy();

echo "session_gc() return value: ";
var_dump($result);

echo "all expired files removed: ";
var_dump(!file_exists($files[0]) && !file_exists($files[1]) && !file_exists($files[2]));
?>
--EXPECT--
session_gc() return value: int(3)
all expired files removed: bool(true)
