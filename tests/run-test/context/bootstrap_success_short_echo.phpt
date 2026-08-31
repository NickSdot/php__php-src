--TEST--
Test context bootstrap - short echo tags - Workspace is available
--FILE--
<?= Workspace::file('short.txt')->name, "\n" ?>
--EXPECT--
short.txt
