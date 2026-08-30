--TEST--
GH-21797: Phar::webPhar() NULL dereference when SCRIPT_NAME absent from SAPI environment
--CGI--
--EXTENSIONS--
phar
--INI--
phar.readonly=0
phar.require_hash=0
variables_order=EGPC
register_argc_argv=0
cgi.fix_pathinfo=0
--ENV--
REQUEST_METHOD=GET
PATH_INFO=/gh21797.phar
--FILE--
<?php
$archive = TestPath::file('gh21797.phar');
$fname = $archive->path;
$phar = new Phar($fname);
$phar->addFromString('index.php', '<?php echo "ok\n"; ?>');
$phar->setStub('<?php
Phar::webPhar();
echo "no crash\n";
__HALT_COMPILER(); ?>');
unset($phar);
include $fname;
?>
--EXPECT--
no crash
