--TEST--
Context bootstrap makes TestPath available in short echo PHPT sections
--FILE--
<?= TestPath::file('short.txt')->name, "\n" ?>
--EXPECT--
short.txt
