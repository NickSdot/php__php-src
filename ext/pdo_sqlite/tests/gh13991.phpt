--TEST--
Fix GH-13984: Buffer size is now checked before memcmp
--EXTENSIONS--
pdo_sqlite
--FILE--
<?php
$dbfile = Workspace::file();
$db = new PDO('sqlite:' . $dbfile, null, null, [PDO::ATTR_PERSISTENT => true]);
echo 'done!';
?>
--EXPECT--
done!
