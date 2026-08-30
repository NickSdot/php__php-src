--TEST--
GH-20302 (Freeing a phar alias may invalidate PharFileInfo objects)
--EXTENSIONS--
phar
--INI--
phar.require_hash=0
--FILE--
<?php
$archive = TestPath::file('gh20302.phar');
$fname = $archive->path;
$pname = 'phar://' . $fname;
$file = "<?php
__HALT_COMPILER(); ?>";
$files = array();
$files['here'] = 'a';
include __DIR__.'/files/phar_test.inc';
$b = new PharFileInfo($pname . '/here');

// Create new phar with same alias and open it
$directory = TestPath::directory('gh20302');
@mkdir($directory->path);
$archive2 = $directory->file('gh20302.phar');
$fname = $archive2->path;
$pname = 'phar://' . $fname;
include __DIR__.'/files/phar_test.inc';
try {
    new Phar($fname);
} catch (UnexpectedValueException $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}
?>
--EXPECTF--
UnexpectedValueException: Cannot open archive "%sgh20302.phar", alias is already in use by existing archive
