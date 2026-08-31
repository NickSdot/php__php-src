--TEST--
ISSUE #149 (Phar mount points not working this OPcache enabled)
--INI--
opcache.enable=1
opcache.enable_cli=1
phar.readonly=0
--EXTENSIONS--
opcache
phar
--CONFLICTS--
server
--FILE--
<?php
$stub = "<?php header('Content-Type: text/plain;');
Phar::mount('this.file', '". __FILE__ . "');
echo 'OK\n';
__HALT_COMPILER(); ?>";
Workspace::useRelative();
$archive = Workspace::file('archive.phar.php');
$archiveUrl = str_replace(__DIR__ . '/', '', $archive->path);
$p = new Phar($archive, 0, 'this');
$p['index.php'] = "";  # A Phar must have at least one file, hence this dummy
$p->setStub($stub);
unset($p);

include "php_cli_server.inc";

$ini = '-d opcache.enable=1 -d opcache.enable_cli=1';
if (file_exists(ini_get('extension_dir').'/phar.'.PHP_SHLIB_SUFFIX)) {
    $ini .= ' -d extension=phar.'.PHP_SHLIB_SUFFIX;
}
php_cli_server_start($ini);

echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $archiveUrl);
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $archiveUrl);
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $archiveUrl);
?>
--EXPECT--
OK
OK
OK
