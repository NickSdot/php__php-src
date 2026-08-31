--TEST--
Bug GH-8846: Delayed early binding can be used for classes without parents
--EXTENSIONS--
opcache
--CONFLICTS--
server
--INI--
opcache.validate_timestamps=1
opcache.revalidate_freq=0
--FILE--
<?php

Workspace::useRelative();
$index = Workspace::file('gh8846-index.php');
file_put_contents($index, <<<'PHP'
<?php
if (!@$_GET['skip']) {
    include dirname(__DIR__) . '/gh8846-1.inc';
}
include dirname(__DIR__) . '/gh8846-2.inc';
echo "Ok\n";
PHP);

include 'php_cli_server.inc';
php_cli_server_start('-d opcache.enable=1 -d opcache.enable_cli=1');

$url = 'http://' . PHP_CLI_SERVER_ADDRESS . '/' . basename($index->directory) . '/' . $index->name;
echo file_get_contents($url);
echo "\n";
echo file_get_contents("$url?skip=1");
?>
--EXPECTF--
bool(true)
<br />
<b>Fatal error</b>:  Cannot redeclare class Foo (previously declared in %sgh8846-1.inc:2) in <b>%sgh8846-2.inc</b> on line <b>%d</b><br />
Stack trace:
#0 %sgh8846-index.php(%d): include()
#1 {main}

bool(true)
Ok
