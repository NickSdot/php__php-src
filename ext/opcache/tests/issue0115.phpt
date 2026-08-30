--TEST--
ISSUE #115 (path issue when using phar)
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
$stub = '<?php
Phar::interceptFileFuncs();
require "phar://this/index.php";
__HALT_COMPILER(); ?>';
TestContext::useRelativeWorkspace();
$archive1 = TestPath::file('archive1.phar.php');
$archive2 = TestPath::file('archive2.phar.php');
$archive1Url = str_replace(__DIR__ . '/', '', $archive1->path);
$archive2Url = str_replace(__DIR__ . '/', '', $archive2->path);
$p = new Phar($archive1, 0, 'this');
$p['index.php'] = '<?php
  echo "Hello from Index 1.\n";
  require_once "phar://this/hello.php";
';
$p['hello.php'] = "Hello World 1!\n";
$p->setStub($stub);
unset($p);
$p = new Phar($archive2, 0, 'this');
$p['index.php'] = '<?php
  echo "Hello from Index 2.\n";
  require_once "phar://this/hello.php";
';
$p['hello.php'] = "Hello World 2!\n";
$p->setStub($stub);
unset($p);

include "php_cli_server.inc";

$ini = '-d opcache.enable=1 -d opcache.enable_cli=1';
if (file_exists(ini_get('extension_dir').'/phar.'.PHP_SHLIB_SUFFIX)) {
    $ini .= ' -d extension=phar.'.PHP_SHLIB_SUFFIX;
}
php_cli_server_start($ini);

echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $archive1Url);
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $archive2Url);
?>
--EXPECT--
Hello from Index 1.
Hello World 1!
Hello from Index 2.
Hello World 2!
