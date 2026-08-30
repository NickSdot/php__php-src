--TEST--
Fix GH-13984: Buffer size is now checked before memcmp
--EXTENSIONS--
pdo_sqlite
--SKIPIF--
<?php if (file_exists(getcwd() . '/13991db')) die('skip File "13991db" already exists.'); ?>
--FILE--
<?php
$dbfile = TestPath::file();
$db = new PDO('sqlite:' . $dbfile, null, null, [PDO::ATTR_PERSISTENT => true]);
echo 'done!';
?>
--EXPECT--
done!
